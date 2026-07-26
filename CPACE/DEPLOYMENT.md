# Branching & Deployment Workflow

## Branches
- `dev` — active work branch. Push here, test locally against your XAMPP MySQL
  (`cpace_db`). Nothing here touches the live site.
- `main` — production. Hostinger's Git integration auto-deploys `main` into
  `public_html/app` on every push. Only merge `dev` → `main` when a change
  has been tested locally and you're ready for it to go live immediately.

## Local workflow (dev)
1. `git checkout dev`.
2. Work and commit as normal, `git push origin dev`.
3. Test locally: `php artisan serve` (or XAMPP vhost) against the local
   `.env` (already configured for `mysql` / `cpace_db` / root).
4. When ready to ship: merge `dev` → `main` and `git push origin main` —
   this triggers the Hostinger auto-deploy into `public_html/app`.

## Server layout (already set up, 2026-07-27)
The repo root isn't the Laravel app — the app lives in a `CPACE/` subfolder
(repo root also has `README.md` / `documents/`). Hostinger's Git deploy
directory is configured (hPanel → cpace.site → Advanced → GIT → the ⋮ menu
→ "Change root directory") to deploy into `public_html/app`, so on the
server the real path is:

```
public_html/app/CPACE/          <- Laravel root (composer.json, artisan, .env)
public_html/app/CPACE/public/   <- Laravel's public/ folder
public_html/.htaccess           <- bridges the domain root into the above
```

`public_html/.htaccess` (NOT part of the git repo — created once directly
on the server, survives every redeploy):
```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/app/CPACE/public/
RewriteRule ^(.*)$ app/CPACE/public/$1 [L]
</IfModule>
```

If you ever need to recreate this (e.g. new site from scratch), that's the
one-time step — it's not something `git push` touches.

## Production `.env`
`.env` is gitignored — never pushed. `.env.production.example` (tracked in
this repo) is the template. On the server it lives at
`public_html/app/CPACE/.env`, `chmod 600`.

Key differences from the Laravel-default template — matched to what
actually exists in the Hostinger DB dump (see project memory: DB was loaded
from a SQL dump, not artisan migrations, and has no `sessions`/`cache`/
`jobs` tables):
- `SESSION_DRIVER=file` (not `database` — no `sessions` table exists)
- `CACHE_STORE=file` (not `database` — no `cache` table exists)
- `QUEUE_CONNECTION=sync` (not `database` — no `jobs` table exists)
- `DB_DATABASE=u562417869_CPACE`, `DB_USERNAME=u562417869_CPACE_username`
  (Hostinger prefixes db/user names with the account ID), `DB_HOST=localhost`
- `APP_KEY` — generate fresh on the server, don't reuse the local one:
  `php artisan key:generate --force`
- Google OAuth: add `https://cpace.site/auth/google/callback` as an
  authorized redirect URI in Google Cloud Console before relying on it in
  prod — the local callback URL won't work there.

## Deploy steps (run over SSH after each push to main)
SSH access: hPanel → Advanced → SSH Access (enable it, add a public key
under "SSH keys" rather than sharing a password).

```
cd ~/domains/cpace.site/public_html/app/CPACE
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**`storage:link` doesn't work here** — Hostinger disables PHP's `symlink`
and `exec` (security hardening on shared hosting), so
`php artisan storage:link` fatals with "Call to undefined function
exec()". Create the symlink directly via the SSH shell instead (one-time,
survives redeploys since it lives outside git):
```
ln -s ../storage/app/public public/storage
```

**No Node/npm on the server.** Build frontend assets locally and upload
the compiled output after any frontend change:
```
npm run build
scp -P 65002 -i ~/.ssh/cpace_hostinger -r public/build \
    u562417869@46.202.186.227:~/domains/cpace.site/public_html/app/CPACE/public/build
```

`--force` on `migrate` is required because `APP_ENV=production` blocks
interactive migration prompts.
