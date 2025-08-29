# Laravel SSO Demo (API-first UI)

This is a demo project showing how to integrate the [muhammadsalman/laravel-sso](https://packagist.org/packages/muhammadsalman/laravel-sso) package into a fresh Laravel app.  
It demonstrates:

- API-first routes (`/api/sso/...`)
- Tailwind-based frontend UI (`resources/views/sso.blade.php`)
- Support for multiple providers (Twitter with PKCE, Google, Apple, Facebook, GitHub, LinkedIn, Microsoft)
- Session-based auth (no database required)
- OAuth callback → Redirects back to Home
- Current session viewer
- Provider redirect testing
- Full callback & test history log
- Fun sparkles ✨ in UI

---

## ⚡ Requirements

- PHP 8.1+
- Laravel 10+
- Composer
- Node.js (optional, for Tailwind dev build)
- Valid OAuth credentials for your providers (Twitter, Google, etc.)

---

## 🚀 Quick Start in 60 Seconds

```bash
# 1. Clone this demo
git clone https://github.com/yourname/laravel-sso-demo.git
cd laravel-sso-demo

# 2. Install dependencies
composer install

# 3. Serve the project
php artisan serve

#Then open:
http://127.0.0.1:8000

```bash
composer require muhammadsalman/laravel-sso:^1.0

```bash
php artisan vendor:publish --tag=laravel-sso-config

```dotenv
# --- Twitter / X (PKCE used only for Twitter in your package) ---
SSO_TWITTER_CLIENT_ID=xxx
SSO_TWITTER_CLIENT_SECRET=xxx   # leave empty if your X app is Public
SSO_TWITTER_REDIRECT=http://localhost:8000/api/sso/callback/twitter

# --- Google ---
SSO_GOOGLE_CLIENT_ID=xxx
SSO_GOOGLE_CLIENT_SECRET=xxx
SSO_GOOGLE_REDIRECT=http://localhost:8000/api/sso/callback/google

# --- Apple ---
SSO_APPLE_CLIENT_ID=xxx
SSO_APPLE_CLIENT_SECRET=xxx
SSO_APPLE_REDIRECT=http://localhost:8000/api/sso/callback/apple

# --- Facebook ---
SSO_FACEBOOK_CLIENT_ID=xxx
SSO_FACEBOOK_CLIENT_SECRET=xxx
SSO_FACEBOOK_REDIRECT=http://localhost:8000/api/sso/callback/facebook

# --- GitHub ---
SSO_GITHUB_CLIENT_ID=xxx
SSO_GITHUB_CLIENT_SECRET=xxx
SSO_GITHUB_REDIRECT=http://localhost:8000/api/sso/callback/github

# --- LinkedIn ---
SSO_LINKEDIN_CLIENT_ID=xxx
SSO_LINKEDIN_CLIENT_SECRET=xxx
SSO_LINKEDIN_REDIRECT=http://localhost:8000/api/sso/callback/linkedin

# --- Microsoft ---
SSO_MICROSOFT_CLIENT_ID=xxx
SSO_MICROSOFT_CLIENT_SECRET=xxx
SSO_MICROSOFT_REDIRECT=http://localhost:8000/api/sso/callback/microsoft


#📚 API Routes
| Method | Endpoint                       | Description                                    |
| ------ | ------------------------------ | ---------------------------------------------- |
| GET    | `/`                            | UI demo page (Blade + Tailwind)                |
| GET    | `/api/sso/providers`           | List configured providers                      |
| GET    | `/api/sso/redirects`           | Bulk build redirect URLs                       |
| GET    | `/api/sso/redirect/{provider}` | Single redirect URL                            |
| GET    | `/api/sso/callback/{provider}` | OAuth callback → sets session & redirects home |
| GET    | `/api/sso/me`                  | Current session info                           |
| POST   | `/api/sso/logout`              | Logout (clear session)                         |
| GET    | `/api/sso/tests`               | Run redirect-URL tests for all providers       |
| GET    | `/api/sso/history`             | View callback/test history                     |
| POST   | `/api/sso/history/clear`       | Clear history                                  |


🎨 UI Features

Sign-in buttons
Each provider shows a button with proper SVG icon + label.

Current session box
Shows avatar, name, username/email.

Test runner
Run redirect URL tests for all providers in one click.

History log
Maintains all callback + test attempts with timestamp & results.

Sparkles ✨
Adds small animated sparkles for success actions.

Screenshot:

### Sign-in UI
![Demo Screenshot](https://raw.githubusercontent.com/engrsalmankamboh/sso-demo/main/docs/screenshot.png)

### History Log
![History Screenshot](https://raw.githubusercontent.com/engrsalmankamboh/sso-demo/main/docs/history.png)

🎨 UI Features

Sign-in buttons
Each provider shows a button with proper SVG icon + label.

Current session box
Shows avatar, name, username/email.

Test runner
Run redirect URL tests for all providers in one click.

History log
Maintains all callback + test attempts with timestamp & results.

Sparkles ✨
Adds small animated sparkles for success actions.

Screenshot:

✅ Flow

User clicks a provider button → goes to OAuth consent page.

Provider redirects back to /api/sso/callback/{provider}.

Controller verifies code, stores session, logs history.

Redirects back to / with ?auth=1 or ?auth=0.

Home page JS reads flags, shows toast + sparkles, fetches /api/sso/me and updates UI.

Test runs and history visible in UI.

🧪 Testing

Run Tests button → checks each provider’s redirect URL, logs results.

History section → shows full log of callbacks + tests.

Clear History button → reset logs.

📜 License

MIT — free to use and modify.
