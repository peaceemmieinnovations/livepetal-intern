# Deploying to cPanel — livepetalintern.com.ng

Deployment bundle and steps for shared cPanel (Jupiter) hosting.
The bundle already contains: application code, a **no-dev** `vendor/` (no Composer needed on the server),
prebuilt Vite assets (`public/build/`), and the package manifest. No Node.js needed either.

**Upload file:** `livepetal-upload.zip` (~19 MB) — in this folder, delete it after deploying.
**This guide stays out of the zip. Do not publish it — it contains the APP_KEY.**

---

## 1. Preflight in cPanel

1. **MultiPHP Manager** → set the domain `livepetalintern.com.ng` to **PHP 8.3 or newer**
   (the app requires PHP `^8.3`; confirm the PHP selector shows 8.3/8.4/8.5 first — if the host
   only offers ≤8.2 the app will not run and you need to ask the host to enable a newer PHP).
   Confirm extensions are active: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`,
   `ctype`, `json`, `fileinfo`, `curl`, `gd`.
2. **MySQL® Databases** → create database + user (example names), assign the user to the
   database with **ALL PRIVILEGES**. Note the full prefixed names, e.g.
   `livepetalintern_db`, `livepetalintern_user`, and the password you set.

## 2. Upload the application

1. **File Manager** → home directory → create a folder `livepetal-app`
   (it must sit **next to** `public_html`, not inside it).
2. Upload `livepetal-upload.zip` into `/home/livepetalintern/`, then right-click → **Extract**.
   It extracts a folder named `vendorbuild` → rename `vendorbuild` to `livepetal-app`.
   Final layout:

   ```
   /home/livepetalintern/
     livepetal-app/          ← Laravel (vendor/ included)
     public_html/            ← webroot, mostly empty
   ```

## 3. Point public_html at the app

1. **Delete** the default contents of `public_html` (index.html, cgi-bin, etc.).
2. Create `public_html/index.php`:

   ```php
   <?php

   require __DIR__.'/../livepetal-app/public/index.php';
   ```

   This is safe: the app resolves its base path from its own file location
   (`bootstrap/app.php` → `basePath: dirname(__DIR__)`), not from the docroot.
3. Copy `livepetal-app/public/.htaccess` into `public_html/` (handles asset/rewrite rules).

## 4. Create `.env`

Create `/home/livepetalintern/livepetal-app/.env`:

```env
APP_NAME="LivePetal Intern"
APP_ENV=production
APP_KEY=base64:UHFWZ9+rLBFRKFczwJRgz+aZWxuOTEqadp1ccGGTg+w=
APP_DEBUG=false
APP_URL=https://livepetalintern.com.ng

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=livepetalintern_db
DB_USERNAME=livepetalintern_user
DB_PASSWORD=CHANGE_ME

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=465
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="no-reply@livepetalintern.com.ng"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Replace the DB lines with the credentials from step 1. Leave the mail block until you have
real SMTP details (password resets email via this).

## 5. Permissions, link, migrate

Via **Terminal** (cPanel → Terminal), or a one-off PHP script if Terminal is unavailable:

```bash
cd ~/livepetal-app
chmod -R 755 storage bootstrap/cache          # 775 if the owner differs from the process user
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan cache:clear
php artisan view:clear
```

Notes:
- `SESSION_DRIVER`, `CACHE_STORE` and `QUEUE_CONNECTION` are all `database`, so migrations
  create the tables they need — run them before opening the site.
- The bundle already contains `bootstrap/cache/packages.php` + `services.php` (generated from
  the no-dev vendor). If you ever add/remove Composer packages, re-run
  `php artisan package:discover`.
- Seeders are dev-only. The first real user is created by visiting `/register`.
- No Tailwind/npm on the server is required: `public/build/` is prebuilt. If you change
  frontend code, rebuild locally (`npm run build`) and re-upload `public/build`.

## 6. Cron jobs (cPanel → Cron Jobs)

```
* * * * * php /home/livepetalintern/livepetal-app/artisan schedule:run >/dev/null 2>&1
```

If you rely on queued jobs (notifications, PDFs), add a worker:

```
*/5 * * * * php /home/livepetalintern/livepetal-app/artisan queue:work --stop-when-empty >/dev/null 2>&1
```

Otherwise set `QUEUE_CONNECTION=sync` in `.env` to run jobs inline.

## 7. Verify

- `https://livepetalintern.com.ng/up` → returns 200
- `https://livepetalintern.com.ng/` → homepage with built CSS/JS (View Source shows `/build/assets/...`)
- `/login`, `/register` work
- Check `storage/logs/laravel.log` if anything errors (File Manager).

## 8. Security checklist

- `APP_DEBUG=false` and `APP_ENV=production`.
- `.env` lives outside `public_html` — never move it inside.
- Do not keep cPanel passwords or this file in the project folder once deployed.
- Keep `livepetal-upload.zip` out of `public_html`.

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| 500 with no log | Missing PHP ≥ 8.3 or an extension from step 1; check MultiPHP error log |
| "No application encryption key" | `.env` missing or `APP_KEY` empty → `php artisan key:generate` |
| 404 on every page | `.htaccess` not copied to `public_html`, or domain not pointing at it |
| `Unable to locate file in Vite manifest` | `public/build/` not extracted / not rebuilt after a change |
| Blank page under `/build/*` | `public_html/.htaccess` missing → re-copy from `livepetal-app/public/` |
| Migration fails on `sessions` table | `.env` not read (check the path) or DB credentials wrong |
