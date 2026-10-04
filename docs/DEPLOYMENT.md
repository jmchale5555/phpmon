# Deployment Model: Docker in Dev, Shared Hosting in Prod

This document describes how this project moves from the Docker development environment to a
no-build shared host (Hostinger, cPanel, etc.), and the rules that keep that move boring.

The short version: **you deploy code, never state.** Production runs the same application code as
dev; only configuration and file placement differ.

---

## 1. Code vs state

There are exactly three things that are **state**, and they survive every redeploy:

| State | Where it lives | Preserve on redeploy? |
| --- | --- | --- |
| MySQL database (content, settings, form submissions) | the host's MySQL service | always |
| Uploaded images/files | `public/uploads/` | always |
| Configuration/secrets | `.env` | always |

Everything else is **code** and can be replaced wholesale: `app/`, `public/` (except `uploads/`),
`vendor/`, `database/`, `scripts/`, assets, `.htaccess`.

The application never depends on how the schema got there, so the deployed app needs no migration
machinery at runtime.

---

## 2. Framework vs site

`phpmon` is the canonical framework. Each business site is a separate repo generated from it (for
example via GitHub's "Use this template"). Site-specific changes do **not** get pushed back into
`phpmon` except as genuine framework improvements.

| Stays in `phpmon` (framework) | Belongs to each site repo |
| --- | --- |
| `app/core/*`, `App`, `Model`, `Database`, `Session`, `Request`, helpers | content controllers, views, and theme CSS |
| generic `Home` / `_404` controllers and shared partials | the site's content model and `database/` migrations/schema |
| vendored Pico / Alpine / HTMX / icons, `.htaccess`, docker setup | site images and branding |
| `scripts/`, `docs/`, `Makefile`, `.env.example` | the site's `.env` and `public/uploads/` |

**Rule:** fix framework bugs in `phpmon` first, then pull the change into each site. Two copies of
a framework will drift; make the framework the source of truth so drift is a choice, not an
accident.

### Repository workflow

1. Commit framework changes to `phpmon` and push (`git push origin master`).
2. Mark `phpmon` as a **GitHub Template repository** (Settings → Template repository).
3. For each new site, use **"Use this template"** to create a fresh repo (`site-<client>`). This
   starts the site with a clean snapshot and no shared history.
4. In the site repo, add the site-specific pieces: content controllers/views, theme, its own
   migrations/schema, `.env`, and `public/uploads/`.
5. When you fix a framework bug, fix it in `phpmon`, then merge/cherry-pick it into the sites that
   need it.

If you prefer plain remotes instead of templates, create an empty site repo and push a fresh
snapshot into it (avoid `git remote set-url` inside `phpmon` — that would repoint the framework's
own origin).

---

## 3. Schema in development (the migrator stays)

The PHP migrator is a **dev-only authoring tool**. It is how you design, test, and reproduce the
schema locally. It never runs in production.

```bash
make migrate     # apply pending migrations in the dev DB
make seed        # apply pending seeders in the dev DB
make db-status   # show what has been applied
make db-reset    # drop, recreate, migrate, seed
```

Migrations live in `database/migrations/`; each returns
`['up' => function (PDO $pdo): void { ... }]` and is tracked in `schema_migrations`. Tracking
history matters only here, in dev.

---

## 4. Getting schema to production

### First deploy — import a schema dump

After your migrations are in the state you want to ship:

```bash
make schema-dump
```

This writes **structure only** (no data) to `database/schema.sql`. Then, on the host:

1. Create the database and user in the hosting panel.
2. Open **phpMyAdmin** → select the database → **Import** → upload `database/schema.sql`.
3. Add any reference/lookup data (the `make seed` data is dev data; do not blindly import user
   content).

That is deploy #1 done. Because the app never reads `schema_migrations`, the tracking table does
not need to exist in production.

> **Warning:** a full dump (with data) re-imported into a live database is destructive — `mysqldump`
> emits `DROP TABLE IF EXISTS`. Only ever import a full dump into a *fresh* database.

### Rare schema changes — a delta

When the schema must change after launch:

1. Author and test the change in dev with a normal migration.
2. Write a small **delta** `.sql` file containing just the change, e.g.
   `database/updates/20261004_add_phone_to_settings.sql`:
   ```sql
   ALTER TABLE settings ADD COLUMN phone VARCHAR(40) NULL AFTER value;
   ```
3. Apply the delta in dev (or re-run the migration) and verify.
4. Apply the same delta on the host via phpMyAdmin.
5. Back up the production database first (see §7).

Keep the delta files in the repo as the record of production changes. They are the production-side
counterpart to the dev migrations.

> Design for this to be rare. Store editable content in a stable shape (see §6) so adding a blurb
> is a row, not a column.

### Redeploys (code only)

A normal update is: replace code, leave state alone. Build a package and upload it, or upload the
changed files.

```bash
make package
```

This creates `dist/phpmon-<date>.zip` containing code only. It **excludes** `.env`,
`public/uploads/*`, `.git`, `docker/`, `docs/`, `Makefile`, and editor files. On the host:

- web root (`public_html/`) ← contents of the package's `public/`
- one level above the web root ← `app/`, `database/`, `scripts/`, `vendor/`, `.env.example`

Do not overwrite the host's `.env` or `public/uploads/`.

---

## 5. Host layout

Two layouts work, depending on what the host allows.

**A. Set the document root to `public/` (preferred if available):**

```
~/site/
├── app/
├── database/
├── scripts/
├── vendor/          # optional
├── .env
└── public/          # <-- document root
```

**B. Host fixes the web root at `public_html/` (most common on shared hosting):**

```
~/
├── app/             # sibling of public_html, not web-accessible
├── database/
├── scripts/
├── vendor/          # optional
├── .env
└── public_html/     # <-- document root = contents of public/
    ├── index.php
    ├── .htaccess
    └── uploads/     # state; preserve
```

The code resolves the project root as `dirname(document root)`, so in layout B the non-public
directories must sit **directly beside** `public_html/`. If you cannot keep them outside the web
root, deny access to `app/`, `database/`, `scripts/`, `vendor/`, and `.env` with a root
`.htaccess` (`Require all denied`).

---

## 6. Content model (stable JSON)

This framework targets brochure/CMS sites, not high-scale systems, so the schema is designed to
change as little as possible. Content is stored in a stable shape; new editable text is a new key
or row, never a new column.

This schema ships as the optional CMS module (`database/migrations/20261004_*`), so a site that uses
the module gets it via `make migrate`:

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug VARCHAR(190) NOT NULL,
    title VARCHAR(190) NOT NULL,
    content JSON NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(190) NOT NULL,
    setting_value TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    path VARCHAR(255) NOT NULL,
    alt VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Page bodies are a JSON object of key/value blocks edited in the admin UI. Adding "a second phone
number in the footer" becomes a `settings` row or a new page block — no `ALTER TABLE`, no delta, no
deploy ceremony.

---

## 7. Backups

Before any deploy that touches the database (a delta, or a breaking change), export the production
database via phpMyAdmin (Export → SQL) and keep it. `public/uploads/` should be backed up whenever
it changes. With no `down()` migrations, the backup **is** the rollback path.

---

## 8. Preflight

Run the environment check locally and, if you have CLI on the host, there too:

```bash
make preflight                 # in dev
php scripts/preflight.php      # directly, wherever PHP CLI is available
```

It verifies PHP version, required extensions, `putenv` availability (used by the `.env` loader),
`.env` readability, core files, and that `public/uploads/` is writable. It exits non-zero on hard
failures.

---

## 9. Environment differences to keep in mind

- **PHP version.** Dev runs 8.3; `composer.json` pins the platform to **8.1** (the floor, and the
  minimum for Carbon 3). Avoid syntax newer than your host offers.
- **Extensions.** Only `pdo_mysql` is required. Add `gd`/`fileinfo` only if a site introduces image
  handling.
- **`putenv`.** If the host disables it, the `.env` loader cannot work; set `APP_*`/`DB_*` via
  `SetEnv` in `.htaccess` or real environment variables instead.
- **Mail.** Shared-host `mail()` is unreliable; use SMTP for contact forms.
- **Cron.** Rarely needed here (no scheduled jobs by default). If a one-shot job is needed, use a
  temporary cron entry and remove it afterward.
- **Remote MySQL.** If the host permits it, you can point a local `.env` at the production DB and
  run scripts from your machine — but prefer explicit, deliberate deploys.
