# PHP MVC + HTMX + AlpineJS (no build system)

A starting point for projects that need a clean PHP MVC monolith without framework bloat
and without a frontend build pipeline.

- Keeps a simple MVC/OOP foundation.
- Prioritizes readability and low abstraction over framework-like patterns.
- Prefers server-side rendering with small interactive enhancements, not an SPA.
- No npm, no bundler, no CDN, no runtime internet dependency.
- Composer is optional, not required to boot. A small autoloader in `app/core/init.php` loads core,
  models, and controllers. Add Composer packages (for example `nesbot/carbon`) when you need them;
  `public/index.php` includes `vendor/autoload.php` only if it exists.

## Requirements

- PHP 8.1+ with `pdo_mysql` (the only extension the framework requires). Composer's `platform.php`
  is pinned to 8.1.0 so dependencies resolve for that floor.
- MySQL or MariaDB (Docker Compose provides MariaDB 11.4).
- Apache with `mod_rewrite` (or an equivalent rewrite-capable web server).
- Composer (optional; only needed if you add packages).

## Quick start (Docker)

Run these from the project root:

```bash
cp .env.example .env       # optional; compose injects its own defaults
make up-dev-build          # build + start web + db with the dev bind mount
make migrate               # create schema (no-op until you add migrations)
make seed                  # seed data (no-op until you add seeders)
# open http://localhost:8080
```

## Configuration

`app/core/config.php` resolves values in this order: **real environment variables -> project-root
`.env` -> built-in defaults**. Copy `.env.example` to `.env` and edit:

```
APP_URL=http://localhost:8080
APP_NAME="My App"
APP_DESC="A no-build PHP MVC monolith"
DEBUG_MODE=true            # set false in production

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=phpmon
DB_USER=root
DB_PASS=
```

## Project structure

```
public/          Web document root (the only web-exposed directory)
  index.php      Single front controller
  .htaccess      Rewrite rules (used in dev and on shared hosting)
  assets/        Pico CSS, Alpine, HTMX, icons, images
  uploads/       Uploaded state (gitignored; preserve on redeploy)
app/
  core/          Framework internals (router, model, database, session, config)
  controllers/   Convention-routed controllers
  models/        Thin PDO models
  views/         Plain PHP templates + partials/
database/
  migrations/    Timestamped PHP migrations (dev-only authoring)
  seeders/       Timestamped PHP seeders (dev-only)
  updates/       Rare hand-written production delta .sql files
scripts/         CLI tools: migrate, seed, db-status, preflight
docker/          Apache vhost + web Dockerfile
docs/            ARCHITECTURE.md, DEPLOYMENT.md, PROJECT_PLAN.md
```

## Composer (optional)

- `composer.json` starts with **no dependencies**. Add packages normally (`composer require ...`).
- The app boots with or without `vendor/`; `public/index.php` guards the autoload include with
  `is_file()`.
- In the Docker dev stack run `make composer-install` / `make composer-update` (writes `vendor/`
  back to the host via the bind mount).
- `vendor/` is gitignored. For a host without Composer, upload `vendor/` alongside the source or run
  `composer install --no-dev` there.

## Docker commands

- `make up` / `make up-build` - run the base stack (image-copy, closer to production)
- `make down` - stop the base stack
- `make up-dev` / `make up-dev-build` - run the dev stack (bind mount + UID/GID mapping)
- `make down-dev` - stop the dev stack
- `make composer-install` / `make composer-update` - run Composer in the dev container
- `make migrate` - apply pending migrations
- `make seed` - apply pending seeders
- `make db-status` - print migration/seeder status
- `make db-reset` - drop/recreate the dev database, then migrate + seed
- `make schema-dump` - export dev schema (structure only) to `database/schema.sql`
- `make preflight` - run environment checks (PHP version, extensions, `.env`, uploads)
- `make package` - build a code-only release zip in `dist/`, excluding all state
- `make prune-all` - `docker system prune -a --volumes` (destructive)

### SELinux note (Fedora/RHEL-like hosts)

- The dev bind mount uses `:z` in `docker-compose.dev.yml` so SELinux labels are shared safely.
- If you hit 403 errors about missing search permissions, ensure parent path execute bits allow
  traversal (for example `/home/<user>` should be at least `711`) and restart with
  `make down-dev && make up-dev`.

## Database migrations and seeders

- Migrations live in `database/migrations/`; each file returns `['up' => function (PDO $pdo): void]`.
- Seeders live in `database/seeders/`; each file returns `['run' => function (PDO $pdo): void]`.
- Both runners are idempotent and track applied versions in `schema_migrations` / `schema_seeds`.
- Never edit an applied migration; add a new one.
- **The migrator is a dev-only tool.** Production receives a `schema.sql` import, not migrations.
  See `docs/DEPLOYMENT.md`.

## Adding a feature

1. Add a migration in `database/migrations/` and run `make migrate`.
2. Add `app/models/Thing.php` (namespace `Model`, `use Model;`, set `$table` + `$allowedColumns`).
3. Add `app/controllers/Things.php` (namespace `Controller`, `use MainController;`).
4. Add `app/views/things.view.php` and render it with `$this->view('things', $data)`.
5. Link to `/things`.

## Frontend

- `public/assets/css/pico-2-1-1.min.css` - Pico CSS.
- `public/assets/js/alpine-3-15-11.min.js` - Alpine for tiny local UI state (inline in markup).
- `public/assets/js/htmx-2-0-10.min.js` - HTMX for server-driven fragment swaps.
- All assets are local; do not introduce npm, a bundler, or CDN dependencies.

## Running without Docker

Use `docker/apache/000-default.conf` as a reference vhost. The important behavior:

- Serve from `public/` as the document root.
- Rewrite non-file/non-directory routes to `index.php?url=...` (see `public/.htaccess`).
- Keep static assets under `public/assets/...` directly web-accessible.
- If you use Composer packages, run `composer install` from the project root first.

## Deploying to shared hosting (Hostinger, cPanel, etc.)

The app is a no-build PHP app, so it can run on any host with modern PHP. There is no process
manager and no Node. Composer is only needed if you added packages: upload `vendor/` or run
`composer install --no-dev` on the host.

**The one rule: deploy code, never state.** The database, `public/uploads/`, and `.env` are state
and must survive every redeploy. See `docs/DEPLOYMENT.md` for the full model.

**Layout A — host lets you point the document root at `public/`:**

```
~/site/
├── app/  database/  scripts/  vendor/  .env
└── public/          # <-- document root
```

**Layout B — host fixes the web root at `public_html/` (most common):**

```
~/
├── app/  database/  scripts/  vendor/  .env    # siblings of public_html, not web-accessible
└── public_html/                                # <-- document root = contents of public/
    ├── index.php
    ├── .htaccess
    └── uploads/
```

In layout B the non-public directories must sit directly beside `public_html/`, because the app
resolves the project root as `dirname(document root)`.

### First deploy

1. Build the code package: `make package` → `dist/phpmon-<date>.zip`.
2. Upload `public/` contents to `public_html/`; upload `app/`, `database/`, `scripts/`, `vendor/`
   one level above.
3. Create `.env` on the host (production `APP_URL`, `APP_NAME`, `DEBUG_MODE=false`, `DB_*`).
4. Generate and import the schema: `make schema-dump`, then import `database/schema.sql` via
   phpMyAdmin.
5. Ensure `public_html/uploads/` is writable and does not execute PHP (the `index.php` guard is
   already included).
6. Run `php scripts/preflight.php` if you have CLI, and confirm `.env` is not web-accessible.

On later deploys, replace code only — never overwrite the host's `.env`, database, or `uploads/`.

If your host only offers per-directory `SetEnv`, you can set `APP_*` / `DB_*` via `.htaccess`
instead of a `.env` file. No code changes are required.

See `docs/DEPLOYMENT.md` for the code/state model, the dev-only migration workflow, schema deltas,
and packaging, and `docs/ARCHITECTURE.md` for the architectural blueprint.
