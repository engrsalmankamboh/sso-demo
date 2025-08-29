# Laravel SSO Demo (API-first UI)

[![Laravel](https://img.shields.io/badge/Laravel-10+-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB3?logo=php&logoColor=white)](https://www.php.net/)
[![Package](https://img.shields.io/badge/Package-muhammadsalman%2Flaravel--sso-0F766E)](https://packagist.org/packages/muhammadsalman/laravel-sso)
[![License](https://img.shields.io/badge/License-MIT-3B82F6)](#-license)

A complete demo project showing how to integrate the **[`muhammadsalman/laravel-sso`](https://packagist.org/packages/muhammadsalman/laravel-sso)** package into a fresh Laravel app.  
This demo is **API-first** with a lightweight Tailwind UI.

---

## 📑 Table of Contents
1. Overview  
2. Features  
3. Requirements  
4. Installation & Setup  
5. Configuration  
6. API Routes  
7. UI Overview  
8. Authentication Flow  
9. Testing  
10. Screenshots  
11. Troubleshooting  
12. Contributing  
13. License  

---

## 1. Overview

The **Laravel SSO Demo** illustrates how to connect multiple social providers into a Laravel application using `muhammadsalman/laravel-sso`.  

- Supports **Twitter PKCE** + standard OAuth2 for Google, Apple, Facebook, GitHub, LinkedIn, Microsoft  
- Works with **API-first flows** — every call is JSON based  
- Blade UI only consumes these APIs and displays session/test data  
- Session-based (no DB required)  

---

## 2. Features

- 🔑 Multi-provider SSO (Twitter, Google, Apple, Facebook, GitHub, LinkedIn, Microsoft)  
- 🔄 PKCE support for Twitter  
- 🖥 API-first endpoints (`/api/sso/...`)  
- 🎨 Tailwind-based UI with SVG icons  
- 📜 Session-only auth (simple demo, no DB needed)  
- 🧪 Built-in **test runner** for redirect URLs  
- 📂 History log for callbacks and tests  
- ✨ Toasts + sparkles on success  

---

## 3. Requirements

- PHP **8.1+**  
- Laravel **10+**  
- Composer  
- Node.js (optional — if you recompile Tailwind)  
- OAuth credentials for providers  

---

## 4. Installation & Setup

```bash
# Clone the demo
git clone https://github.com/yourname/laravel-sso-demo.git
cd laravel-sso-demo

# Install dependencies
composer install

# Install the SSO package
composer require muhammadsalman/laravel-sso:^1.0

# (Optional) Publish config
php artisan vendor:publish --tag=laravel-sso-config
```

Run the project:

```bash
php artisan serve
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 5. Configuration

Update your `.env` file with provider secrets and redirect URIs:

```dotenv
# Twitter (PKCE)
SSO_TWITTER_CLIENT_ID=xxx
SSO_TWITTER_CLIENT_SECRET=xxx
SSO_TWITTER_REDIRECT=http://localhost:8000/api/sso/callback/twitter

# Google
SSO_GOOGLE_CLIENT_ID=xxx
SSO_GOOGLE_CLIENT_SECRET=xxx
SSO_GOOGLE_REDIRECT=http://localhost:8000/api/sso/callback/google

# Apple
SSO_APPLE_CLIENT_ID=xxx
SSO_APPLE_CLIENT_SECRET=xxx
SSO_APPLE_REDIRECT=http://localhost:8000/api/sso/callback/apple

# Facebook
SSO_FACEBOOK_CLIENT_ID=xxx
SSO_FACEBOOK_CLIENT_SECRET=xxx
SSO_FACEBOOK_REDIRECT=http://localhost:8000/api/sso/callback/facebook

# GitHub
SSO_GITHUB_CLIENT_ID=xxx
SSO_GITHUB_CLIENT_SECRET=xxx
SSO_GITHUB_REDIRECT=http://localhost:8000/api/sso/callback/github

# LinkedIn
SSO_LINKEDIN_CLIENT_ID=xxx
SSO_LINKEDIN_CLIENT_SECRET=xxx
SSO_LINKEDIN_REDIRECT=http://localhost:8000/api/sso/callback/linkedin

# Microsoft
SSO_MICROSOFT_CLIENT_ID=xxx
SSO_MICROSOFT_CLIENT_SECRET=xxx
SSO_MICROSOFT_REDIRECT=http://localhost:8000/api/sso/callback/microsoft
```

---

## 6. API Routes

| Method | Endpoint                       | Description                                    |
| ------ | ------------------------------ | ---------------------------------------------- |
| GET    | `/`                            | UI demo page                                   |
| GET    | `/api/sso/providers`           | List configured providers                      |
| GET    | `/api/sso/redirects`           | Bulk redirect URLs                             |
| GET    | `/api/sso/redirect/{provider}` | Single provider redirect URL                   |
| GET    | `/api/sso/callback/{provider}` | OAuth callback → sets session & redirects home |
| GET    | `/api/sso/me`                  | Current session info                           |
| POST   | `/api/sso/logout`              | Logout (clear session)                         |
| GET    | `/api/sso/tests`               | Run redirect-URL tests                         |
| GET    | `/api/sso/history`             | View callback/test history                     |
| POST   | `/api/sso/history/clear`       | Clear history                                  |

---

## 7. UI Overview

- **Sign-in buttons** with brand icons  
- **Current session** shows avatar, name, username/email  
- **Run tests** verifies redirect URLs  
- **History log** shows callbacks + test attempts  
- **Sparkles** for fun ✨  

---

## 8. Authentication Flow

```
[User clicks provider]
      ↓
[/api/sso/redirects builds URL]
      ↓
[Provider OAuth consent screen]
      ↓
[/api/sso/callback/{provider}]
   → verify code
   → set session (sso.user, sso.provider, sso.raw)
   → append to sso.history
   → redirect to "/?auth=1" or "/?auth=0&error=..."
      ↓
[Home "/"]
   → JS reads flags
   → shows toast/sparkles
   → calls /api/sso/me
   → updates UI
   → shows history/tests
```

---

## 9. Testing

- **Run Tests** button → generates redirect URLs for all providers  
- **History** → keeps callback & test results  
- **Clear History** → resets logs  

---

## 10. Screenshots

### Sign-in UI
![Demo Screenshot](https://raw.githubusercontent.com/engrsalmankamboh/sso-demo/main/docs/screenshot.png)

### History Log
![History Screenshot](https://raw.githubusercontent.com/engrsalmankamboh/sso-demo/main/docs/history.png)

---

## 11. Troubleshooting

### Screenshots not showing
- Files must exist in `docs/` folder in `main` branch  
- Names are **case-sensitive** (`screenshot.png` ≠ `Screenshot.PNG`)  
- Use raw links in README:  
  ```md
  ![Demo](https://raw.githubusercontent.com/engrsalmankamboh/sso-demo/main/docs/screenshot.png)
  ```

### Session empty after callback
- Ensure redirect URIs match exactly (`http://127.0.0.1:8000` vs `http://localhost:8000`)  
- Run `php artisan optimize:clear`  

### Twitter unauthorized_client
- Public apps → no client_secret, PKCE used  
- Confidential apps → set client_secret properly  

---

## 12. Contributing

Contributions are welcome!  
1. Fork the repo  
2. Create a feature branch  
3. Commit your changes  
4. Open a Pull Request  

---

## 13. License

This project is open-sourced under the **MIT License**.
