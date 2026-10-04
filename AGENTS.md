# AGENTS.md

## What This Repo Is Now
- Treat this as a fresh PHP MVC monolith that keeps only the simple MVC/OOP foundation from the legacy codebase.
- Prioritize readability and low abstraction over framework-like patterns or excessive indirection.
- Prefer server-side rendering with small interactive enhancements; do not evolve this into an SPA architecture.

## Non-Negotiable Frontend Direction
- No Node/npm build pipeline, no `node_modules`, no bundler output dependency, no CDN/runtime internet requirement.
- Use local static files only (source files are in repo root, served from `public/assets/css/` and `public/assets/js/`):
  - `pico-2-1-1.min.css`
  - `alpine-3-15-11.min.js`
  - `htmx-2-0-10.min.js`
- HTMX: server-driven interactions (`hx-get`, `hx-post`, fragment swaps), server remains source of truth.
- Alpine: only lightweight local UI state (toggles, dropdowns, tiny form UX), inline with markup.
- Layout target stays simple: title, navbar, main section, footer.

## Current Wiring You Will Trip Over
- Entrypoint: `public/index.php` (defines `ROOTPATH` = public root and `APPROOT` = project root).
- Routing: `public/.htaccess` rewrites non-file/non-dir URLs to `index.php?url=...`. The Docker vhost uses `AllowOverride All` so the same file is the single source of rewrite rules.
- Controller resolution is convention-based in `app/core/App.php`:
  - `/foo/bar` -> `app/controllers/Foo.php` -> `\Controller\Foo::bar()`.
- View rendering uses direct PHP includes via `MainController::view()` in `app/core/Controller.php`.
- Autoloading is a small `spl_autoload_register` in `app/core/init.php`. Composer is optional: `public/index.php` includes `vendor/autoload.php` only if it exists.
- Configuration precedence: real env vars -> project-root `.env` -> defaults in `app/core/config.php`.
- `Home` is a neutral landing page. An optional CMS module ships in the normal folders:
  - auth: `Controller\Login/Logout/Password`, `Model\User`, `Core\RequiresLogin`
  - content: `Controller\Admin/Page`, `Model\Page/Setting/Media` (stable-JSON page bodies)
  - see `README.md` "Optional CMS module" for routes and the file list to remove it.
- Shared layout partials already include local static assets:
  - `app/views/partials/header.view.php` includes `assets/css/pico-2-1-1.min.css`
  - `app/views/partials/footer.view.php` includes `assets/js/alpine-3-15-11.min.js` and `assets/js/htmx-2-0-10.min.js`

## Infra and Data Constraints
- Docker Compose provides Apache + modern PHP + MariaDB for dev.
- Apache must serve from `public/`; rewrite behavior lives in `public/.htaccess`.
- PHP floor is 8.1 (pinned in `composer.json` `config.platform`); keep code 8.1-compatible.
- PHP migrations + seeders are the **dev-only** source of truth for schema. Production receives SQL
  (`make schema-dump` -> import `database/schema.sql`; rare deltas live in `database/updates/`).
  Never make production depend on the migrator.

## Deployment Model (see docs/DEPLOYMENT.md)
- Deploy code, never state. State = database, `public/uploads/`, and `.env`; preserve all three.
- `make package` builds a code-only release zip in `dist/` (excludes state and dev files).
- `make preflight` / `php scripts/preflight.php` checks the target environment.
- `phpmon` is the canonical framework; each business site is a separate repo derived from it.

## Existing Config Gotcha
- `app/core/config.php` reads `APP_*` / `DB_*` from env, then `.env`, then defaults.
- Only `pdo_mysql` is treated as a required PHP extension; add `gd`/`fileinfo` only when a site needs image handling.
- Composer is optional and `composer.json` starts empty. Keep the `vendor/autoload.php` include in `public/index.php` guarded with `is_file()` so the app boots without `vendor/`.
