# Branching & Deployment Workflow

## Branches
- `dev` — active work branch. Push here, test locally against your XAMPP MySQL
  (`cpace_db`). Nothing here touches the live site.
- `main` — production. Hostinger's Git integration auto-deploys on every push
  to `main`. Only merge `dev` → `main` when a change has been tested locally
  and you're ready for it to go live immediately.

## Local workflow (dev)
1. `git checkout dev` (already the checked-out branch after this session).
2. Work and commit as normal, `git push origin dev`.
3. Test locally: `php artisan serve` (or XAMPP vhost) against the local
   `.env` (already configured for `mysql` / `cpace_db` / root).
4. When ready to ship: open a PR `dev` → `main`, or merge locally and
   `git push origin main` — this triggers the Hostinger auto-deploy.

## Before the first production deploy — fix the document root
The repo root is `README.md` / `documents/` / `CPACE/` (this folder). The
actual Laravel app's `public/` folder is at `CPACE/public`, **two levels**
below repo root. Hostinger's document root is currently set to the repo
root itself, which means `.env`, `app/`, `vendor/`, etc. would be
web-accessible once deployed — a security problem, not just a config nit.

Fix in hPanel before pushing to `main` for real:
1. hPanel → Websites → cpace.site → Manage → Advanced → **document root** /
   PHP configuration.
2. Point the document root at `<repository-path>/CPACE/public`
   (the exact `<repository-path>` is whatever folder hPanel's Git feature
   clones this repo into — check Hostinger's Git deployment settings for
   the deploy path it uses).
3. Save and confirm `https://cpace.site` serves Laravel's welcome/login
   page, not a directory listing.

If hPanel's plan doesn't allow a custom document root, the fallback is a
public_html "bridge" `index.php` that requires `CPACE/public/index.php`
with adjusted `$_SERVER` paths — ask if you land in that situation.

## Production `.env`
`.env` and `.env.production` are gitignored — they're never pushed. Use
`.env.production.example` (tracked in this repo) as the template:

1. On the server, copy it to `.env` inside `CPACE/` (the Laravel root):
   `cp .env.production.example .env`
2. Fill in real values — none of these should ever be committed:
   - `DB_PASSWORD` — the CPACE database password.
   - `MAIL_PASSWORD`, `GOOGLE_CLIENT_SECRET`, `GEMINI_API_KEY`,
     `OPENROUTER_API_KEY` — copy from your local `.env` or generate fresh
     production keys if you want to keep local/prod usage separate.
   - `DB_HOST` — confirm it's `localhost` in hPanel → Databases → CPACE
     (that's the Hostinger shared-hosting default; double-check it hasn't
     changed).
3. Generate a **new** `APP_KEY` for production — don't reuse the local one:
   `php artisan key:generate` (run once on the server after `.env` exists).
4. If Google OAuth is used, add `https://cpace.site/auth/google/callback`
   as an authorized redirect URI in Google Cloud Console — the local
   callback URL won't work in production.

## Deploy steps (run on the server after each auto-deploy, or via a
Hostinger post-deploy script if supported)
```
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
npm ci && npm run build
```
`--force` is required for `migrate` because `APP_ENV=production` blocks
interactive migration prompts.
