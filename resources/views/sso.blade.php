<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Laravel SSO Demo (API-first)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* tiny sparkles */
    .sparkle { position: fixed; pointer-events: none; opacity: 0; animation: pop 900ms ease forwards; }
    @keyframes pop {
      0%   { transform: translateY(0) scale(0.6) rotate(0deg); opacity: 0; }
      20%  { opacity: 1; }
      100% { transform: translateY(-60px) scale(1) rotate(180deg); opacity: 0; }
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 text-slate-800">
  <div class="mx-auto p-6">
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Laravel SSO (API-first)</h1>
        <p class="text-slate-600">Powered by <span class="font-medium">muhammadsalman/laravel-sso</span></p>
      </div>
      <div class="flex items-center gap-2">
        <button id="runTests" class="rounded-xl px-3 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-sm">Run tests</button>
        <button id="clearHistory" class="rounded-xl px-3 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-sm">Clear history</button>
        <button id="logoutBtn" class="hidden rounded-xl px-4 py-2 bg-slate-900 text-white hover:bg-slate-700 transition">
          Logout
        </button>
      </div>
    </header>

    <main class="mt-10 grid xl:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-8">
      <!-- Sign-in -->
      <section class="xl:col-span-1 bg-white/80 backdrop-blur rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-semibold">Sign in</h2>
          <span id="provStatus" class="text-xs text-slate-500">Loading providers…</span>
        </div>
        <p class="text-slate-600 mb-4">We’ll prepare each provider’s redirect URL first, then show the buttons.</p>

        <div class="mb-4 flex items-center gap-3">
          <label for="platform" class="text-sm text-slate-600">Platform:</label>
          <select id="platform" class="text-sm rounded-lg border border-slate-300 bg-white px-3 py-2">
            <option value="web" selected>web</option>
            <option value="android">android</option>
            <option value="ios">ios</option>
          </select>
          <button id="reloadProviders" class="ml-auto text-sm rounded-lg border border-slate-300 bg-white px-3 py-2 hover:bg-slate-50">
            Reload providers
          </button>
        </div>

        <div id="providers" class="space-y-3">
          <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
          <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
          <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
        </div>

        <div id="error" class="hidden mt-4 text-sm text-red-600"></div>
      </section>

      <!-- Session -->
      <section class="xl:col-span-1 bg-white/80 backdrop-blur rounded-2xl shadow-sm p-6 border border-slate-200">
        <h2 class="text-lg font-semibold mb-4">Current Session</h2>
        <div id="sessionBox" class="text-sm space-y-3">
          <div class="h-5 bg-slate-100 rounded animate-pulse w-2/3"></div>
          <div class="h-5 bg-slate-100 rounded animate-pulse w-1/2"></div>
        </div>
      </section>

      <!-- Tests & History -->
      <section class="xl:col-span-1 space-y-8">
        <div class="bg-white/80 backdrop-blur rounded-2xl shadow-sm p-6 border border-slate-200">
          <h2 class="text-lg font-semibold mb-4">Latest Test Run</h2>
          <div id="testsBox" class="text-sm text-slate-700">
            <p class="text-slate-500">No tests yet.</p>
          </div>
        </div>

        <div class="bg-white/80 backdrop-blur rounded-2xl shadow-sm p-6 border border-slate-200">
          <h2 class="text-lg font-semibold mb-4">History (Callbacks & Tests)</h2>
          <div id="historyBox" class="text-sm text-slate-700 space-y-3 max-h-[420px] overflow-auto">
            <p class="text-slate-500">No history yet.</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-6 right-6 hidden">
      <div class="bg-slate-900 text-white px-4 py-3 rounded-xl shadow-lg text-sm" id="toastMsg"></div>
    </div>
  </div>

  <script>
    const el = (sel) => document.querySelector(sel);
    const providersBox = el('#providers');
    const provStatus = el('#provStatus');
    const errorBox = el('#error');
    const logoutBtn = el('#logoutBtn');
    const sessionBox = el('#sessionBox');
    const testsBox = el('#testsBox');
    const historyBox = el('#historyBox');
    const toast = el('#toast');
    const toastMsg = el('#toastMsg');
    const platformSel = el('#platform');
    const reloadBtn = el('#reloadProviders');
    const runTestsBtn = el('#runTests');
    const clearHistoryBtn = el('#clearHistory');

    // tiny sparkles (suskay vibes ✨)
    function sparkles(x, y, count = 10) {
      for (let i = 0; i < count; i++) {
        const s = document.createElement('div');
        const size = Math.random() * 6 + 4;
        s.className = 'sparkle';
        s.style.left = (x + (Math.random()*80 - 40)) + 'px';
        s.style.top = (y + (Math.random()*10 - 5)) + 'px';
        s.style.width = size + 'px';
        s.style.height = size + 'px';
        s.style.background = ['#22c55e','#06b6d4','#a855f7','#f59e0b','#ef4444'][Math.floor(Math.random()*5)];
        s.style.borderRadius = '2px';
        s.style.transform = `rotate(${Math.random()*360}deg)`;
        document.body.appendChild(s);
        setTimeout(() => s.remove(), 900);
      }
    }

    const showToast = (msg, ok = true) => {
      toastMsg.textContent = msg;
      toastMsg.parentElement.classList.toggle('bg-emerald-600', ok);
      toastMsg.parentElement.classList.toggle('bg-slate-900', !ok);
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 2200);
    };

    async function fetchJSON(url, opts = {}) {
      const res = await fetch(url, { credentials: 'same-origin', ...opts });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.error || `HTTP ${res.status}`);
      return data;
    }

    const providerIcons = {
      twitter: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1227" class="w-5 h-5 fill-sky-500"><path d="M1199.6 216.6c-44.4 19.7-92.2 32.9-142.3 38.8 51.2-30.7 90.5-79.4 109-137.2-47.8 28.3-100.8 48.7-157.2 59.7C962.2 128 899.4 96 829.8 96c-135.5 0-245.1 109.7-245.1 245.2 0 19.2 2.2 38 6.4 55.9-203.7-10.2-384.6-107.9-505.5-256.4-21.1 36.2-33.2 78.2-33.2 123.1 0 85 43.3 160.1 109.1 204.1-40.2-1.3-78.1-12.3-111.2-30.7v3.1c0 118.8 84.6 217.9 197 240.3-20.6 5.6-42.3 8.6-64.7 8.6-15.8 0-31.2-1.5-46.2-4.3 31.3 97.6 122.1 168.7 229.6 170.6-84.1 66-190.1 105.4-305.4 105.4-19.9 0-39.5-1.2-58.8-3.4 109.0 70.2 238.5 111.3 377.7 111.3 453.2 0 700.9-375.4 700.9-700.8 0-10.7-.2-21.5-.7-32.2 48.2-34.8 90-78.1 123.1-127.6z"/></svg>`,
      google: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="w-5 h-5"><path fill="#4285F4" d="M24 9.5c3.54 0 6.72 1.22 9.23 3.6l6.85-6.85C35.88 2.56 30.47 0 24 0 14.63 0 6.48 5.38 2.56 13.22l7.98 6.2C12.18 13.37 17.63 9.5 24 9.5z"/><path fill="#34A853" d="M46.1 24.5c0-1.65-.15-3.24-.42-4.77H24v9.04h12.48c-.54 2.91-2.18 5.38-4.66 7.04l7.17 5.57C42.44 37.56 46.1 31.62 46.1 24.5z"/><path fill="#FBBC05" d="M10.54 28.5c-.48-1.4-.75-2.9-.75-4.5s.27-3.1.75-4.5l-7.98-6.2C.92 17.48 0 20.65 0 24s.92 6.52 2.56 10.7l7.98-6.2z"/><path fill="#EA4335" d="M24 48c6.47 0 11.88-2.13 15.83-5.79l-7.17-5.57c-2 1.34-4.6 2.11-8.66 2.11-6.37 0-11.82-3.87-13.46-9.92l-7.98 6.2C6.48 42.62 14.63 48 24 48z"/></svg>`,
      apple: `<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-black" viewBox="0 0 384 512"><path d="M318.7 268c-.5-37.6 16.4-65.9 50-86.7-18.8-27.4-47.1-42.3-84.7-44.9-35.6-2.5-74.5 20.8-88.2 20.8-14.2 0-47-19.8-72.8-19.3-52.5.8-108.4 30.9-108.4 122.1 0 29.9 11 61.3 24.7 84.9 23.2 39.3 54 83.3 93.1 82.1 22.7-.6 38.8-15.6 69-15.6 29.6 0 45.1 15.6 72.9 15.1 39.2-.6 67.8-42.1 90.7-81.7 15.6-27.1 22.1-53.5 22.3-54.8-.5-.2-43-16.5-43.6-65.1zM251.1 53.1c18.2-21.8 30.5-52.1 27.2-82.1-26.3 1-57.9 17.5-76.6 39.3-16.7 18.7-31.3 49.1-27.4 78.1 29.5 2.3 58.8-14.5 76.8-35.3z"/></svg>`,
      facebook: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="w-5 h-5 fill-blue-600"><path d="M279.14 288l14.22-92.66h-88.91V127.89c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S293.08 0 262.23 0c-73.19 0-121.09 44.38-121.09 124.72v70.62H86.41V288h54.73v224h100.17V288z"/></svg>`,
      github: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" class="w-5 h-5 fill-gray-800"><path d="M248 8C111 8 0 119 0 256c0 110 71.3 203.5 170.2 236.5 12.4 2.3 17-5.4 17-12v-42.7c-69.3 15-84-33.4-84-33.4-11-28-27.1-35.3-27.1-35.3-22.2-15 1.7-14.6 1.7-14.6 24.6 1.7 37.7 25.2 37.7 25.2 21.8 37.7 57.3 26.8 71.4 20.5 2.3-15.8 8.9-26.8 16.2-33-55.3-6.3-113.5-27.7-113.5-123.7 0-27.3 9.8-49.5 25.7-67-2.6-6.3-11.1-32.2 2.4-67.2 0 0 20.9-6.7 68.5 25.6 19.9-5.5 41.4-8.3 62.6-8.4 21.2 0 42.6 2.9 62.6 8.4 47.6-32.3 68.5-25.6 68.5-25.6 13.5 35 4.9 60.9 2.4 67.2 15.9 17.5 25.7 39.7 25.7 67 0 96-58.3 117.3-113.9 123.5 9.2 7.9 17.5 23.5 17.5 47.5v70.2c0 6.6 4.6 14.3 17 12C424.7 459.5 496 366 496 256 496 119 385 8 248 8z"/></svg>`,
      linkedin: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5 fill-blue-700"><path d="M100.3 448H7V148.9h93.3V448zm-46.7-341c-29.9 0-54.1-24.3-54.1-54.1 0-29.8 24.2-54 54.1-54 29.8 0 54 24.2 54 54 0 29.9-24.2 54.1-54 54.1zM447.9 448h-93.4V304.1c0-34.3-12.3-57.7-43.1-57.7-23.5 0-37.6 15.8-43.8 31.1-2.3 5.5-2.9 13.2-2.9 20.9V448H171.3s1.2-260.6 0-287.1h93.3v40.7c12.4-19.2 34.6-46.7 84.1-46.7 61.3 0 106.9 40 106.9 125.7V448z"/></svg>`,
      microsoft: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23" class="w-5 h-5"><rect width="11" height="11" x="1" y="1" fill="#f25022"/><rect width="11" height="11" x="12" y="1" fill="#7fba00"/><rect width="11" height="11" x="1" y="12" fill="#00a4ef"/><rect width="11" height="11" x="12" y="12" fill="#ffb900"/></svg>`,
    };

    function renderButtons(items) {
      providersBox.innerHTML = '';
      items.forEach(p => {
        if (p.ok && p.redirect_url) {
          const a = document.createElement('a');
          a.href = p.redirect_url;
          a.className = 'flex items-center gap-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 px-4 py-3 transition';
          a.innerHTML = `${providerIcons[p.key] || ''}<span class="font-medium">${p.label}</span>`;
          providersBox.appendChild(a);
        } else {
          const div = document.createElement('div');
          div.className = 'flex items-center justify-between rounded-xl border border-red-200 bg-red-50 px-4 py-3';
          div.innerHTML = `
            <span class="flex items-center gap-2 text-red-700">${providerIcons[p.key] || ''}${p.label}</span>
            <span class="text-xs text-red-600" title="${p.error ?? 'Unavailable'}">unavailable</span>
          `;
          providersBox.appendChild(div);
        }
      });
    }

    function renderTests(run) {
      if (!run) {
        testsBox.innerHTML = `<p class="text-slate-500">No tests yet.</p>`;
        return;
      }
      const when = new Date(run.at).toLocaleString();
      const rows = run.results.map(r => `
        <tr class="${r.ok ? 'bg-emerald-50' : 'bg-red-50'}">
          <td class="px-3 py-2 font-medium">${r.provider}</td>
          <td class="px-3 py-2">${r.ok ? 'OK' : 'FAILED'}</td>
          <td class="px-3 py-2 text-slate-600">${r.message || ''}</td>
        </tr>
      `).join('');
      testsBox.innerHTML = `
        <div class="text-xs text-slate-500 mb-2">Platform: <b>${run.platform}</b> • ${when}</div>
        <div class="overflow-auto border border-slate-200 rounded-lg">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-slate-700">
              <tr>
                <th class="px-3 py-2 text-left">Provider</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Message</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
      `;
    }

    function renderHistory(items) {
      if (!items || !items.length) {
        historyBox.innerHTML = `<p class="text-slate-500">No history yet.</p>`;
        return;
      }
      historyBox.innerHTML = items.slice().reverse().map(h => {
        const when = new Date(h.at).toLocaleString();
        const badge = h.ok
          ? `<span class="text-emerald-700 bg-emerald-50 border border-emerald-200 text-[11px] px-2 py-0.5 rounded-full">OK</span>`
          : `<span class="text-red-700 bg-red-50 border border-red-200 text-[11px] px-2 py-0.5 rounded-full">ERR</span>`;
        const extra = h.userinfo || h.oauth
          ? `<pre class="mt-2 text-xs bg-slate-50 border border-slate-200 rounded-lg p-2 overflow-x-auto">${JSON.stringify({userinfo:h.userinfo, oauth:h.oauth}, null, 2)}</pre>`
          : '';
        return `
          <div class="border border-slate-200 rounded-xl p-3">
            <div class="flex items-center gap-2">
              <div class="text-sm font-medium">${(h.type || 'event').toUpperCase()}</div>
              <div class="text-xs text-slate-500">• ${h.provider}</div>
              <div>${badge}</div>
              <div class="ml-auto text-xs text-slate-500">${when}</div>
            </div>
            <div class="mt-1 text-sm text-slate-700">${h.message || ''}</div>
            ${extra}
          </div>
        `;
      }).join('');
    }

    async function loadProvidersWithRedirects() {
      provStatus.textContent = 'Loading providers…';
      errorBox.classList.add('hidden');
      const platform = platformSel.value || 'web';
      try {
        const data = await fetchJSON(`/api/sso/redirects?platform=${encodeURIComponent(platform)}`);
        renderButtons(data.items || []);
        const failed = (data.items || []).filter(i => !i.ok).length;
        provStatus.textContent = failed ? `Loaded (some unavailable: ${failed})` : 'Loaded';
      } catch (e) {
        provStatus.textContent = 'Failed';
        errorBox.textContent = e.message || 'Failed to prepare provider redirects.';
        errorBox.classList.remove('hidden');
      }
    }

    async function refreshSession() {
      const data = await fetchJSON('/api/sso/me');
      logoutBtn.classList.toggle('hidden', !data.is_authenticated);

      if (data.user) {
        sessionBox.innerHTML = `
          <div class="flex items-center gap-3">
            ${data.user.avatar ? `<img src="${data.user.avatar}" class="w-10 h-10 rounded-full border border-slate-200">` : ''}
            <div>
              <div class="font-medium">${data.user.name ?? '—'}</div>
              <div class="text-slate-500">@${data.user.username ?? data.user.email?.replace(/@.*/, '') ?? '-'}</div>
            </div>
          </div>
          <div class="text-slate-600">
            <span class="inline-flex items-center gap-2 text-xs rounded-full border border-emerald-200 bg-emerald-50 px-2 py-1">
              Authenticated via <strong>${data.provider ?? '—'}</strong>
            </span>
          </div>
          <pre class="mt-3 text-xs bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto">${JSON.stringify(data.user, null, 2)}</pre>
        `;
      } else {
        sessionBox.innerHTML = `<p class="text-slate-600">Not signed in.</p>`;
      }
    }

    async function loadHistory() {
      const data = await fetchJSON('/api/sso/history');
      renderHistory(data.history || []);
      // show latest test
      const tests = data.tests || [];
      renderTests(tests.length ? tests[tests.length - 1] : null);
    }

    function readQueryFlags() {
      const q = new URLSearchParams(window.location.search);
      const auth = q.get('auth');   // "1" or "0"
      const err  = q.get('error');  // error message
      if (auth === '1') {
        showToast('Signed in successfully 🎉', true);
        const rect = toast.getBoundingClientRect();
        sparkles(rect.left + rect.width/2, rect.top); // sparkles near toast
      }
      if (auth === '0' && err) showToast(decodeURIComponent(err), false);

      // clean URL
      if (auth || err) {
        const url = new URL(window.location.href);
        url.searchParams.delete('auth');
        url.searchParams.delete('error');
        window.history.replaceState({}, '', url.toString());
      }
    }

    logoutBtn.addEventListener('click', async () => {
      await fetchJSON('/api/sso/logout', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      });
      showToast('Logged out');
      await refreshSession();
    });

    reloadBtn.addEventListener('click', async () => {
      providersBox.innerHTML = `
        <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
        <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
        <div class="h-11 bg-slate-100 rounded-xl animate-pulse"></div>
      `;
      await loadProvidersWithRedirects();
    });

    runTestsBtn.addEventListener('click', async (e) => {
      const rect = e.currentTarget.getBoundingClientRect();
      sparkles(rect.left + rect.width/2, rect.top);
      const platform = platformSel.value || 'web';
      const res = await fetchJSON(`/api/sso/tests?platform=${encodeURIComponent(platform)}`);
      showToast('Tests completed');
      renderTests(res.run);
      await loadHistory();
    });

    clearHistoryBtn.addEventListener('click', async (e) => {
      const rect = e.currentTarget.getBoundingClientRect();
      sparkles(rect.left + rect.width/2, rect.top, 14);
      await fetchJSON('/api/sso/history/clear', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      });
      showToast('History cleared');
      await loadHistory();
    });

    // init
    (async () => {
      readQueryFlags();
      await loadProvidersWithRedirects();
      await refreshSession();
      await loadHistory();
    })();
  </script>
</body>
</html>
