# Staging Deployment (for CI test automation)

A second, non-production deployment so Playwright/Postman CI runs (and any
future test automation) never touch real student/faculty data or production
`main`. Mirrors `DEPLOYMENT.md`'s setup for `main`, but tracks `dev` instead
and gets its own subdomain, database, and `.env`.

Proposed subdomain: **`staging.cpace.site`** (a free subdomain on the same
Hostinger plan as `cpace.site` — no extra hosting cost). Rename anywhere
below if you'd rather use something else.

## 1. Create the subdomain
hPanel → cpace.site → **Domains → Subdomains** → create `staging` (becomes
`staging.cpace.site`). Hostinger will assign it a document root — note the
exact path it shows you (something like
`domains/cpace.site/public_html/staging` or a sibling `domains/staging.cpace.site/public_html`,
depending on panel version).

## 2. Create a separate database
hPanel → **Databases → MySQL Databases** → create a new database + user
(e.g. `..._CPACE_staging` / `..._CPACE_staging_user` — Hostinger will prefix
both with your account ID same as production's `u562417869_CPACE`). **Do
not reuse the production database** — staging needs to be freely resettable
without any risk to real data.

## 3. Wire Git deploy to `dev`
hPanel → the new `staging.cpace.site` site → **Advanced → GIT** → connect
`github.com/kristine9030/CPACE`, branch **`dev`** (not `main`). Same "Change
root directory" trick as production (`DEPLOYMENT.md` step 18) — point it at
this site's own `app` folder so the repo's `CPACE/` subfolder lands at
`.../app/CPACE`, then create that site's own `public_html/.htaccess` (same
content as production's, shown in `DEPLOYMENT.md`) since each site needs
its own copy — it's created directly on the server, not part of the repo.

## 4. Staging `.env`
Copy `.env.production.example` as a starting point, then change:
```
APP_ENV=staging
APP_URL=https://staging.cpace.site
APP_KEY=                          # leave blank, generate fresh (see step 5) —
                                   # unlike prod, staging's DB has no
                                   # encrypted data yet, so key:generate is fine here
DB_DATABASE=<the staging DB name from step 2>
DB_USERNAME=<the staging DB user from step 2>
DB_PASSWORD=<its password>
DB_HOST=localhost
MAIL_MAILER=log                   # IMPORTANT: prevents CI test runs from
                                   # ever emailing a real inbox (OTP mails,
                                   # password resets, etc. just get logged)
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```
(Google OAuth login won't work on staging unless you also add
`https://staging.cpace.site/auth/google/callback` in Google Cloud Console —
skip it; the automated tests should log in via the demo-account
email/password fields, not social login.)

## 5. Load the schema + demo accounts
hPanel → phpMyAdmin → the staging database → **Import** →
`database/cpace_database.sql` from this repo. This is the same dump used
for a fresh local install, so it already seeds the demo accounts the
Playwright specs will log in as: `chair@cpace.test`, `superadmin@cpace.test`,
etc. (passwords are in that file / `login.blade.php`'s demo buttons).

Then, over SSH:
```
cd ~/domains/cpace.site/public_html/<staging path from step 1>/app/CPACE
php artisan key:generate --force      # staging only — never do this on prod
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force           # catches staging up to migrations added after the dump was cut
php artisan config:cache
php artisan route:cache
php artisan view:cache
ln -s ../storage/app/public public/storage
```
Frontend assets: same as prod — `npm run build` locally, `scp` the `public/build`
folder up (`DEPLOYMENT.md`'s exact command, swap the destination path).

## 6. Confirm it's live
Visit `https://staging.cpace.site/login` and sign in with the demo Super
Admin button — should land on `/superadmin/dashboard`.

## 7. Wire it into CI
1. On staging, log in as Super Admin → **API Tokens** → issue one named
   `GitHub Actions`.
2. GitHub repo → **Settings → Secrets and variables → Actions** → add:
   - `CPACE_APP_URL` = `https://staging.cpace.site`
   - `CPACE_TEST_REPORTS_TOKEN` = the token from step 7.1
3. `.github/workflows/playwright.yml` and `postman.yml` already trigger on
   pushes to `dev` (matching this doc) — once the secrets exist, the next
   push to `dev` runs them for real.

## Keeping staging in sync
Since Git deploy tracks `dev`, staging updates automatically on every push
to `dev` — no separate deploy step needed day to day. Re-run `php artisan
migrate --force` over SSH after any push that adds a migration (deploy
doesn't run it automatically, same as production).
