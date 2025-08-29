<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Muhammadsalman\LaravelSso\Core\SSOManager;

class SsoController extends Controller
{
    public function __construct(private SSOManager $sso) {}

    private function providerList(): array
    {
        return [
            ['key' => 'twitter',   'label' => 'Continue with X (Twitter)'],
            ['key' => 'google',    'label' => 'Continue with Google'],
            ['key' => 'apple',     'label' => 'Continue with Apple'],
            ['key' => 'facebook',  'label' => 'Continue with Facebook'],
            ['key' => 'github',    'label' => 'Continue with GitHub'],
            ['key' => 'linkedin',  'label' => 'Continue with LinkedIn'],
            ['key' => 'microsoft', 'label' => 'Continue with Microsoft'],
        ];
    }

    private function pushSession(string $key, mixed $value): void
    {
        $all = session($key, []);
        $all[] = $value;
        session([$key => $all]);
    }

    public function providers()
    {
        return response()->json([
            'ok' => true,
            'providers' => $this->providerList(),
        ]);
    }

    public function redirects(Request $req)
    {
        $platform = $req->query('platform', 'web');
        $items = [];

        foreach ($this->providerList() as $p) {
            $key = $p['key'];
            try {
                $url = $this->sso->redirectUrl($key, $platform);
                $items[] = [
                    'key'          => $key,
                    'label'        => $p['label'],
                    'redirect_url' => $url,
                    'ok'           => true,
                ];
            } catch (\Throwable $e) {
                $items[] = [
                    'key'          => $key,
                    'label'        => $p['label'],
                    'redirect_url' => null,
                    'ok'           => false,
                    'error'        => $e->getMessage(),
                ];
            }
        }

        return response()->json(['ok' => true, 'items' => $items]);
    }

    public function redirect(Request $req, string $provider)
    {
        $platform = $req->query('platform', 'web');

        try {
            $url = $this->sso->redirectUrl($provider, $platform);
            return response()->json([
                'ok' => true,
                'provider' => $provider,
                'redirect_url' => $url,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    // OAuth callback → store result in session history, set session, then redirect home
    public function callback(Request $req, string $provider)
    {
        $code     = $req->query('code');
        $platform = $req->query('platform', 'web');

        if (!$code) {
            $entry = [
                'at'       => Carbon::now()->toIso8601String(),
                'provider' => $provider,
                'ok'       => false,
                'type'     => 'callback',
                'message'  => 'Authorization code not received',
            ];
            $this->pushSession('sso.history', $entry);
            return redirect('/?auth=0&error=' . urlencode('Authorization code not received'));
        }

        try {
            $data = $this->sso->verifyCode($provider, $code, $platform);
            $ui   = $data['userinfo'] ?? [];
            $oauth= $data['oauth'] ?? [];

            // store minimal session (current user)
            session([
                'sso.provider' => $provider,
                'sso.user' => [
                    'id'       => $ui['id'] ?? $ui['sub'] ?? null,
                    'name'     => $ui['name'] ?? null,
                    'username' => $ui['username'] ?? null,
                    'email'    => $ui['email'] ?? null,
                    'avatar'   => $ui['avatar'] ?? null,
                ],
                'sso.raw' => $data,
            ]);

            // append to history log
            $entry = [
                'at'       => Carbon::now()->toIso8601String(),
                'provider' => $provider,
                'ok'       => true,
                'type'     => 'callback',
                'message'  => 'Login success',
                'userinfo' => Arr::only($ui, ['id','sub','name','username','email','avatar']),
                'oauth'    => Arr::only($oauth, ['token_type','expires_in','scope']),
            ];
            $this->pushSession('sso.history', $entry);

            return redirect('/?auth=1');
        } catch (\Throwable $e) {
            $entry = [
                'at'       => Carbon::now()->toIso8601String(),
                'provider' => $provider,
                'ok'       => false,
                'type'     => 'callback',
                'message'  => $e->getMessage(),
            ];
            $this->pushSession('sso.history', $entry);

            return redirect('/?auth=0&error=' . urlencode($e->getMessage()));
        }
    }

    // Run a "test" for all providers: can we build redirect URLs?
    public function tests(Request $req)
    {
        $platform = $req->query('platform', 'web');
        $results  = [];
        foreach ($this->providerList() as $p) {
            try {
                $this->sso->redirectUrl($p['key'], $platform);
                $results[] = ['provider' => $p['key'], 'ok' => true, 'message' => 'redirect ok'];
            } catch (\Throwable $e) {
                $results[] = ['provider' => $p['key'], 'ok' => false, 'message' => $e->getMessage()];
            }
        }

        $run = [
            'at'      => Carbon::now()->toIso8601String(),
            'platform'=> $platform,
            'results' => $results,
        ];

        // store whole run & also append each result to history (type=test)
        $this->pushSession('sso.tests', $run);
        foreach ($results as $r) {
            $this->pushSession('sso.history', [
                'at'       => $run['at'],
                'provider' => $r['provider'],
                'ok'       => $r['ok'],
                'type'     => 'test',
                'message'  => $r['message'],
            ]);
        }

        return response()->json(['ok' => true, 'run' => $run]);
    }

    public function history()
    {
        return response()->json([
            'ok'      => true,
            'history' => session('sso.history', []),
            'tests'   => session('sso.tests', []),
        ]);
    }

    public function clearHistory()
    {
        session()->forget(['sso.history', 'sso.tests']);
        return response()->json(['ok' => true]);
    }

    public function me()
    {
        return response()->json([
            'ok' => true,
            'is_authenticated' => session()->has('sso.user'),
            'provider' => session('sso.provider'),
            'user' => session('sso.user'),
        ]);
    }

    public function logout()
    {
        session()->forget(['sso.user', 'sso.provider', 'sso.raw']);
        return response()->json(['ok' => true]);
    }
}
