# Architectural Blueprint & Onboarding Guide

> Scope: this document describes the repository as a **no-build, low-abstraction PHP MVC
> monolith boilerplate**, records the template-specific leftovers that were generalized, and lists
> prioritized recommendations that preserve the project's stated principles.
>
> **Status:** §8 (template-specific auth/demo code), §9's low-risk fixes, and the §10 P0 items have
> been implemented — see §14 for the exact changes. Remaining open decisions are in §13.

---

## 1. What This Project Is

A server-rendered PHP MVC monolith:

- No npm, no bundler, no CDN, no runtime internet dependency.
- Frontend is [Pico CSS](https://picocss.com/) + Alpine.js + HTMX, all vendored as local static files.
- Convention-based routing maps URL segments to controller classes and methods.
- Raw PDO is used through a thin `Model` trait. No ORM.
- Database schema is owned by a small project-native PHP migration/seeder runner.
- Dev/run workflow is Docker Compose (Apache + PHP 8.3 + MariaDB); production is intended to be a
  plain shared-hosting deployment (e.g. Hostinger) with modern PHP + JS.

Guiding principle throughout: **readability and low abstraction beat framework-like indirection.**

---

## 2. Repository Map

```
.
├── public/                     # Web document root (only this is web-exposed)
│   ├── index.php               # Single entry point / front controller
│   ├── .htaccess               # rewrites + static asset caching/compression
│   ├── robots.txt
│   ├── assets/                 # local Pico / Alpine / HTMX / icons / images
│   └── uploads/                # uploaded state (guards + gitignore; preserve)
├── app/
│   ├── core/                   # Framework internals
│   │   ├── App.php             # Router / dispatcher
│   │   ├── Controller.php      # `MainController` trait: view rendering
│   │   ├── Model.php           # `Model` trait: CRUD over PDO
│   │   ├── Database.php        # `Database` trait: PDO connect + query helpers
│   │   ├── Session.php         # `Core\Session`
│   │   ├── Request.php         # `Core\Request`
│   │   ├── functions.php       # helpers (esc, redirect, csrf_*, error handling)
│   │   ├── config.php          # env/.env-driven constants (DB_*, APP_*, ROOT)
│   │   └── init.php            # autoloader + core requires
│   ├── controllers/            # Controller\* classes (Home, _404); add as needed
│   ├── models/                 # Model\* classes; add as needed
│   └── views/                  # Plain PHP templates + partials/
├── database/
│   ├── migrations/             # Timestamped PHP migrations (dev-only authoring)
│   ├── seeders/                # Timestamped PHP seeders (dev-only)
│   └── updates/                # rare hand-written production delta .sql files
├── scripts/                    # CLI: db.php, migrate, seed, db-status, preflight
├── docker/                     # dev web Dockerfile + vhost (AllowOverride All)
├── docker-compose.yml
├── docker-compose.dev.yml
├── Makefile
├── composer.json               # optional; starts with no dependencies
├── README.md
├── AGENTS.md
└── docs/                       # ARCHITECTURE.md, DEPLOYMENT.md, PROJECT_PLAN.md
```

---

## 3. Request Lifecycle

```
Apache (docroot = public/)
   │  non-file / non-dir  ──rewrite──▶  public/index.php?url=<path>
   ▼
public/index.php
   ├─ require vendor/autoload.php
   ├─ session_start()
   ├─ min-PHP check
   ├─ define('ROOTPATH', __DIR__)
   ├─ require app/core/init.php      # autoloader + config/functions/core requires
   ├─ DEBUG_MODE → display_errors
   └─ (new App)->loadController()
          │
          ▼
      App::splitURL()      # $_GET['url'] → ['controller','method', ...args]
          │
          ▼
      require app/controllers/<Ucfirst>.php
      new \Controller\<Name>()
          ├─ if 2nd segment is a public, non-underscore method → call it
          └─ else → instantiate \Controller\_404
          │
          ▼
      Controller\X::method(...args)
          ├─ (optional) new Model\Y; $y->first()/insert()/update()/delete()
          └─ $this->view('name', $data)  # MainController trait
                 │
                 ▼
             extract($data); require app/views/name.view.php
                 │ includes partials/header.view.php
                 │   └─ includes partials/navbar.view.php
                 │ renders body
                 └─ includes partials/footer.view.php  # loads Alpine + HTMX
```

**Routing rules (`app/core/App.php`)**

| URL | Resolves to |
| --- | --- |
| `/` or `/home` | `Controller\Home::index()` |
| `/foo/bar` | `Controller\Foo::bar()` |
| `/foo/bar/a/b` | `Controller\Foo::bar('a','b')` |
| unknown controller | `Controller\_404::index()` (HTTP 404) |
| unknown/underscore method | `Controller\_404::index()` (HTTP 404) |

- The default controller/method is `Home`/`index` (`App.php:6-7`, `App.php:11`).
- Methods beginning with `_` are not routable (`App.php:44-46`).
- Class and method names are case-normalized only via `ucfirst`; only the first character.

**Frontend model**

- `partials/header.view.php` includes Pico and scoped layout CSS, opens `<main id="page-content">`.
- `partials/navbar.view.php` uses HTMX (`hx-get`, `hx-target="#page-content"`, `hx-select-oob="#site-nav"`, `hx-push-url`) for full-page-in-place navigation, and Alpine for the account menu.
- Views are full documents that `include` header/footer. This means HTMX selects `#page-content > *` and out-of-band swaps `#site-nav`.

---

## 4. Core Components Reference

| Component | Type | Responsibility | Notes / Risk |
| --- | --- | --- | --- |
| `App` | class (global ns) | URL split + dispatch | No HTTP verb awareness, no route params beyond positional args |
| `MainController` | trait (`Controller\`) | `view($name, $data)` | `extract($data)`; falls back to `404.view.php` |
| `Model` | trait (`Model\`) | CRUD helpers | `all/where/first/between/insert/update/delete`; see §10 |
| `Database` | trait (`Model\`) | PDO connect + `query`/`execute`/`get_row` | Cached connection; `query` returns `false` for empty result |
| `Core\Session` | class | session data + `auth`/`is_logged_in`/`user` | Cookie hardened (httponly, samesite, secure); `regenerate()` for post-login |
| `Core\Request` | class | wrapper for `$_POST`/`$_GET`/`$_FILES`/`$_REQUEST` | No validation layer (validate in models/controllers) |
| `functions.php` | global fns | `esc`, `redirect`, `get_image`, `message`, `URL`, `old_*`, `csrf_*`, `check_extensions`, `register_error_handling` | Debug helpers `show`/`dd` are DEBUG_MODE-only |
| `config.php` | constants | `DB_*`, `APP_*`, `ROOT`, `DEBUG_MODE` | Precedence: env → project-root `.env` → defaults |

> Removed during the generalization pass: `Core\Image`, `Core\Pager`, and the legacy HTML/image
> editor helpers (`add_root_to_images`, `remove_images_from_content`, `delete_images_from_content`)
> plus `get_pagination_vars`/`get_date`. They were unused and outside the low-abstraction brief.

---

## 5. Data Layer

**Migrations** (`scripts/migrate.php`, `database/migrations/*.php`)

- Each file returns `['up' => function (PDO $pdo): void {...}]`.
- Applied versions tracked in `schema_migrations`; already-applied files are skipped.
- Runner fails fast on the first error and records the version only on success.
- No `down()` support.

**Seeders** (`scripts/seed.php`, `database/seeders/*.php`)

- Each file returns `['run' => function (PDO $pdo): void {...}]`.
- Tracked in `schema_seeds`; must be idempotent (`ON DUPLICATE KEY UPDATE` in the example).

**Current shipped schema**

- `users` table (migration `20260425_000001`) + index/check tuning (`20260425_000002`).
- Default seeded user `admin@example.com` / `password` (seeder `20260425_000001`) — **change in real projects.**

---

## 6. Docker / Local Workflow

- `docker-compose.yml` — `web` (built from `docker/web/Dockerfile`, PHP 8.3 + Apache, docroot `public/`) and `db` (mariadb:11.4, named volume). Ports: web `8080:80`, db `3307:3306`.
- `docker-compose.dev.yml` — bind-mounts the repo to `/var/www/html`, maps Apache run user/group to host UID/GID, uses `:z` for SELinux.
- `Makefile` targets: `up`, `up-build`, `down`, `up-dev`, `up-dev-build`, `down-dev`, `composer-install`, `composer-update`, `migrate`, `seed`, `db-status`, `db-reset`, `prune-all`.
- Config is passed as container environment variables (see `docker-compose.yml:9-18`).

Typical first run:

```bash
make up-dev-build      # build + start with bind mount
make migrate           # create schema
make seed              # seed admin@example.com / password
# open http://localhost:8080
```

---

## 7. Deployment Target: Shared Hosting (Hostinger-style)

The no-build goal means the finished app should run from static files + PHP with no process
manager, no Node, and no remote VM. The current repo is **not yet deployable to a typical shared
host** without changes. Gaps, in order of severity:

1. **No `.htaccess` in `public/`.** The repo depends entirely on the Apache vhost
   (`docker/apache/000-default.conf:20-23`). On shared hosting you usually cannot edit the vhost.
   A `public/.htaccess` with the equivalent rewrite is required.
2. **Document root limitation.** Shared hosts normally expose `public_html/`, not an arbitrary
   `public/` subfolder. Either flatten the web root, or add a root `.htaccess` that rewrites into
   `public/` (and move everything else above the web root).
3. **Hard `vendor/autoload.php` requirement** (`public/index.php:3`) even though `composer.json`
   has no autoload map and the only dependency (`nesbot/carbon`) is unused. On a host without
   Composer/`vendor`, the app dies. Either drop the require or guard it.
4. **Env-only config** (`app/core/config.php`) with no `.env` loader and no `.env.example` (the
   README references one that does not exist). On shared hosting, set env via `.htaccess`
   `SetEnv`, or ship a local config file fallback.
5. **Over-strict extension gate** (`app/core/functions.php:9-42`) hard-dies unless
   `gd, mysqli, pdo_mysql, pdo_sqlite, curl, fileinfo, intl, exif, mbstring` are all loaded.
   The current minimal app only actually uses `pdo_mysql`, `gd`, and `fileinfo`. This will reject
   otherwise-fine hosts.
6. **Writable paths.** Image upload/resize writes to the uploads folder; shared hosting needs
   correct directory permissions and the folder must not be executable PHP.

---

## 8. Template-Specific Code That Must Be Generalized

These are project/auth-template artifacts, not generic framework code. They should be removed or
reduced to a clearly labelled optional starter module before publishing as a boilerplate.

### 8.1 Auth feature (currently baked into the template)

| File | Why it is template-specific |
| --- | --- |
| `app/controllers/Login.php` | Hardcodes email/password POST flow and `Model\User` |
| `app/controllers/Logout.php` | Auth-specific; reads `$_SESSION['USER']` directly |
| `app/controllers/Signup.php` | Hardcodes `User` validation + `created_at` handling |
| `app/controllers/Password.php` | Hardcodes password-change flow against `User` |
| `app/models/User.php` | App-specific table, columns, and validation rules |
| `database/migrations/*users*` | Ships a `users`/`is_admin` schema |
| `database/seeders/*seed_default_user*` | Seeds a demo admin credential |
| `app/views/login.view.php`, `signup.view.php`, `password.view.php` | Auth UI |
| `partials/navbar.view.php` | Branching on `$_SESSION['USER']` for login/logout links |

Recommendation: keep auth as a **clearly isolated optional module** (a `README` section plus a
single command/instruction to remove it), or move it under something like `app/modules/auth/`.
Do not leave it intertwined with `Home`, `navbar`, and seeded migrations as the default.

### 8.2 Demo/placeholder content

| File | Issue |
| --- | --- |
| `app/controllers/Home.php` | Reads `$_SESSION['USER']`, calls `get_image('assets/images/peach.png')`, exposes `edit()` demo that `echo`s/`show()`s debug output (`Home.php:23-29`) |
| `app/views/home.view.php` | "Welcome" demo, peach figure, hardcoded auth CTA |
| `public/assets/images/peach.png`, `peach2.png`, `scope.png` | Sample/brand images with no generic purpose |
| `public/assets/images/user.webp`, `no_image.png` | Reasonable defaults, but should be rebrandable |
| `database/seeders/20260425_000001_seed_default_user.php` | Demo credential |

### 8.3 Legacy build-era leftovers

| File | Issue |
| --- | --- |
| `public/resources/pages/book.html` | References removed `./dist/app.js` and Tailwind classes; dead artifact |
| `composer.old` | Empty leftover file |
| `composer.json` + `composer.lock` + `vendor/` | Only dependency `nesbot/carbon` is **never referenced** in code. Either remove it or actually use it. |
| `app/core/Pager.php` | Emits Bootstrap classes (`pagination`, `page-item`, `page-link`, `justify-content-center`) — a Tailwind/Bootstrap-era leftover inconsistent with Pico |
| `app/core/functions.php` `add_root_to_images`, `remove_images_from_content`, `delete_images_from_content` | Legacy rich-text-editor image helpers, not general framework needs |
| `app/core/functions.php` `check_extensions()` | Legacy broad extension list (see §7.5) |
| `public/assets/css/index.php`, `js/index.php`, `images/index.php`, `assets/index.php` | "Access Denied" guards; harmless but `defined('ROOTPATH') or exit` produces a blank 200, not a 403 |
| `Porject_plan.md` | Filename typo (`Porject`); also an internal planning doc, should live under `docs/` or be removed from a public template |
| `.vscode/settings.json` | `"php.validate.executablePath": "/usr/bin"` is not a PHP binary path; IDE-specific config should not ship as a default |

### 8.4 Naming / namespace loose ends

- `App` is a global class while everything else is namespaced. Acceptable, but document it.
- `App.php:37` retains a commented-out line; `Model.php:60-78` retains a large commented-out
  old `where()`; `Database.php:56` retains a stray `// show($con);`. Remove dead code.
- `Model` and `Database` are traits named like classes; `Model` trait does `use \Model\Database`.
  Works, but the naming can confuse newcomers — document or rename to `HasModel`/`HasDatabase`.

---

## 9. Correctness & Robustness Issues

Ordered by likely impact.

1. **`Model::insert()`, `update()`, and `delete()` always `return false`** regardless of outcome
   (`Model.php:146`, `:175`, `:186`). Callers cannot detect failure; `Signup` ignores the result
   entirely. Return `bool`/`lastInsertId()` and check it.
2. **`Database::query()` returns `false` both for "no rows" and "failure"** (`Database.php:19-35`).
   Because errors throw, this is mostly "no rows", but the ambiguity is a footgun. Prefer `[]` for
   an empty result and reserve `false`/exceptions for failure.
3. **New PDO connection on every query** (`Database.php:9-17`). Each `query()`/`get_row()` call
   reconnects. Fine for a tiny app, wasteful under load; a lazily cached connection is trivial.
4. **Unvalidated column identifiers in query builders.** `where()`, `first()`, `between()` interpolate
   array keys directly as column names (`Model.php:23-58`, `:81-103`, `:105-127`). Bound values are
   safe, but identifier injection is possible if keys ever derive from user input. Whitelist against
   `allowedColumns` or document that keys must be developer-controlled.
5. **`Model::all()`/`where()` interpolate `limit`/`offset`/`order_column` directly** and never clamp
   them. Cast `limit`/`offset` to int and validate `order_column`/`order_type`.
6. **`insert()` filters out `created_at`** because it is absent from `User::$allowedColumns`
   (`User.php:12-17`), silently relying on the DB default. Works only because the column has a
   default. Make timestamp/whitelist behavior explicit.
7. **`mime_content_type` requires `fileinfo`**, and GD must be built with `--with-webp` for
   `webp` (`Image.php:39,105`). The Dockerfile configures GD **without** webp
   (`Dockerfile:16`), so webp resize fails silently/incorrectly.
8. **`redirect()` always prefixes `ROOT`** (`functions.php:65-69`). Passing an absolute URL or an
   already-rooted path double-prefixes. Normalize.
9. **`get_image()` relies on CWD-relative `file_exists`** (`functions.php:72-89`). Under CLI or a
   different CWD this misbehaves; resolve against `ROOTPATH`.
10. **`public/index.php` uses relative `require '../vendor/autoload.php'` and `'../app/core/init.php'`**
    (`index.php:3,20`). Use `__DIR__ . '/../...'` to be CWD-independent.
11. **No CSRF protection** on POST forms. The plan lists it as future work; for a CMS-style
    boilerplate that edits content, add a simple token helper.
12. **`Pager` builds links via `str_replace`/regex over `QUERY_STRING`** (`Pager.php:41-56`), which is
    fragile with encoded params. A small query-array rebuild is simpler and safer.
13. **Home/Logout/Password read `$_SESSION['USER']` directly** instead of via `Core\Session`,
    duplicating session knowledge across layers.
14. **Extension autoloader fallback** (`init.php:28-35`) can autoload a wrong class by basename;
    it is redundant with the prefix map. Simplify.

---

## 10. Recommendations (Simplicity-Preserving)

### P0 — required to fulfil the stated deployment goal

1. **Add `public/.htaccess`** mirroring `docker/apache/000-default.conf`, and document the
   "everything above `public/`" layout for shared hosts.
2. **Decouple from Composer at runtime.** Remove the unused `nesbot/carbon` dependency (and
   `composer.old`), and either drop the `vendor/autoload.php` require or wrap it in
   `if (file_exists(...))`. The app's own `init.php` autoloader is sufficient.
3. **Relax `check_extensions()`** to a minimal, real list (`pdo_mysql`, `gd`, `fileinfo`) and make
   it a clear, actionable error — or make it advisory and only fail when the feature is used.
4. **Ship config that works without container env.** Add `.env.example` and a tiny `.env` loader
   (or file-based `config.local.php` fallback) so shared hosting can configure DB creds.
5. **Provide a host-agnostic deploy checklist** in the README (docroot, rewrites, writable uploads,
   disable debug, seed removal).

### P1 — generalization and correctness

6. **Extract the auth feature into an optional, isolated module** (or a documented `make strip-auth`
   / removal guide) so the blank slate has no `User`, auth views, or auth migrations.
7. **Generalize `Home`** to a neutral landing page and delete the `edit()` demo method.
8. **Fix `Model` return values** (`insert` → id/bool, `update`/`delete` → bool) and cache the PDO
   connection in `Database`.
9. **Delete legacy artifacts**: `public/resources/`, `composer.old`, dead code comments, sample
   images with no generic role, and the `book.html`/`dist` references.
10. **Restyle or remove `Pager`** to emit Pico-friendly markup.
11. **Resolve `vendor`/`autoload` + relative-path fragility** (`__DIR__` everywhere).
12. **Add minimal CSRF helper** for POST routes (session token + hidden field + verifier).

### P2 — polish

13. Rename `Porject_plan.md` → `docs/PROJECT_PLAN.md` (or fold the still-useful content into docs).
14. Remove/replace `.vscode/settings.json` with a sane, generic config.
15. Add `make logs` / `make logs-dev` targets (already suggested in the plan).
16. Add smoke tests for routing + auth (even a tiny PHP script that hits each route).
17. Document conventions explicitly: naming, view data passing, model whitelisting, and how to add
    a feature end-to-end.

**Explicitly do NOT add:** a DI container, service providers, an ORM, a router DSL/annotation
engine, an event bus, or a build step. They violate the stated principles.

---

## 11. Onboarding Guide

### 11.1 First 15 minutes

1. Read `README.md`, then this file.
2. `make up-dev-build`, wait for the stack, run `make migrate && make seed`.
3. Browse:
   - `http://localhost:8080/home` — landing page
   - `/login`, `/signup`, `/password`, `/logout` — auth flows
   - `/does-not-exist` — 404 behavior
4. Open `public/index.php`, then `app/core/App.php`, then a controller (`app/controllers/Home.php`),
   then its view. That is the whole request path.

### 11.2 Mental model in one paragraph

Everything is convention. The first URL segment is a controller class file (ucfirst), the second is
a public method, the rest are positional arguments. Controllers return by including a view through
`MainController::view()`. Models are thin PDO wrappers over one table. Views are plain PHP with
local Pico/Alpine/HTMX. There is no container, no service layer, no build. If you need shared logic,
put it in a controller method, a model method, or a global helper in `functions.php` — in that order
of preference.

### 11.3 Recipe: add a new feature end-to-end

Example: a "products" list at `/products`.

1. **Migration** — create `database/migrations/<timestamp>_create_products_table.php`:
   ```php
   <?php
   return ['up' => function (PDO $pdo): void {
       $pdo->exec("CREATE TABLE IF NOT EXISTS products (
           id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
           name VARCHAR(190) NOT NULL,
           created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (id)
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
   }];
   ```
   Run `make migrate`.
2. **Model** — `app/models/Product.php`:
   ```php
   <?php
   namespace Model;
   class Product {
       use Model;
       protected $table = 'products';
       protected $allowedColumns = ['name'];
   }
   ```
3. **Controller** — `app/controllers/Products.php`:
   ```php
   <?php
   namespace Controller;
   defined('ROOTPATH') or exit('Access Denied');
   class Products {
       use MainController;
       public function index() {
           $product = new \Model\Product;
           $this->view('products', ['products' => $product->all()]);
       }
   }
   ```
4. **View** — `app/views/products.view.php` (include `partials/header.view.php` / `footer.view.php`,
   escape output with `esc()`).
5. **Navigate there** with an HTMX link or plain `<a href="<?= ROOT ?>/products">`.

### 11.4 Conventions checklist

- Always `namespace Controller;` / `namespace Model;` and `defined('ROOTPATH') or exit`.
- Escape all dynamic output with `esc()`.
- Use `old_value()`/`old_select()`/`old_checked()` to repopulate forms, and `message()` for
  post-redirect flash messages.
- Add new tables via a migration file; never edit a migration that has been applied — add a new one.
- Set `allowedColumns` on every model before using `insert()`/`update()`.
- Keep JS to Alpine for local UI state and HTMX for server-driven swaps. No new client frameworks.
- Prefer `ROOT . '/path'` for URLs so the app works under any host/subdirectory.

---

## 12. Proposed "Clean Boilerplate" Target Structure

```
app/
├── core/            # unchanged framework internals (minus dead code, Pager tweak)
├── controllers/Home.php   # neutral landing page only
├── models/                # empty (or one example with a clear TODO)
└── views/
    ├── partials/ (header, navbar [no auth branch], footer)
    └── home.view.php      # neutral placeholder
database/
├── migrations/      # empty or one exemplar migration
└── seeders/         # empty
docs/
├── ARCHITECTURE.md
└── PROJECT_PLAN.md
public/
├── .htaccess        # NEW — shared-host rewrite
├── index.php
└── assets/ (pico, alpine, htmx, icons, default placeholder image)
[optional module] auth/  # removable without touching framework core
```

---

## 13. Open Decisions

1. **Auth**: ship as an optional removable module, or remove entirely from the boilerplate and
   provide it as a separate branch/example?
2. **Composer**: keep a minimal Composer setup (PSR-4 autoload, dev-only tooling) or eliminate
   Composer entirely to maximize shared-host portability? **(resolved: kept as optional — see §14)**
3. **Config**: `.env` loader vs. a committed `config.local.php` ignored by git — which fits the
   target hosts better?
4. **Routing**: keep the current 1:1 controller/method convention, or add a small explicit route
   map for CMS-style slugs (e.g. `/page/{slug}`)? A route map is still low-abstraction and improves
   generic UX, but is a deliberate expansion. **(still open)**

---

## 14. Implementation Status (applied)

The following changes were implemented after this analysis. The tree is now a blank, generic
boilerplate.

**P0 — deployment goal**

- Added `public/.htaccess` with the front-controller rewrite and dotfile denial, plus a
  `DirectoryIndex`. Documented the "source above web root, `public/` as `public_html/`" layout and
  the alternative root-`.htaccess` denial approach in `README.md`.
- Composer made **optional** rather than mandatory: removed the unused `nesbot/carbon` dependency
  and `composer.old`; `composer.json` now starts empty. `public/index.php` requires
  `vendor/autoload.php` only when the file exists (`is_file()` guard), so the app boots without a
  `vendor/` directory. `init.php` remains the baseline autoloader. Composer binary/install restored
  in `docker/web/Dockerfile`, and `make composer-install` / `make composer-update` restored.
  `vendor/` is gitignored but can be uploaded to hosts without Composer.
- Relaxed `check_extensions()` to `pdo_mysql`, `gd`, `fileinfo`.
- Added `.env.example` and a dependency-free `.env` loader in `app/core/config.php`
  (precedence: real env -> `.env` -> defaults). `DEBUG_MODE` now defaults to `false`; compose and
  `.env.example` default to `true` for dev.
- Added a shared-hosting deploy checklist to `README.md`.

**Generalization (was §8)**

- Removed the entire auth feature: `Login`/`Logout`/`Signup`/`Password` controllers, `Model\User`,
  auth views, the `users` migrations, and the default-credential seeder.
- `Home` is now a neutral landing page; removed the session/peach/`edit()` demo.
- `partials/navbar.view.php` no longer branches on `$_SESSION['USER']`; removed auth/menu/password
  CSS from `header.view.php`.
- Removed demo/legacy assets: `public/resources/` (`book.html`), `peach*.png`, `scope.png`.
- Generalized `scripts/db-status.php` (no hardcoded `users` table).
- Moved `Porject_plan.md` -> `docs/PROJECT_PLAN.md` and cleaned `.vscode/settings.json`.

**Correctness / robustness fixes (from §9)**

- `Model::insert()`/`update()`/`delete()` now return real success values via a new `Database::execute()`
  and `Database::last_insert_id()`.
- `Database` reuses a single static PDO connection per request.
- `ROOTPATH` (public root) and `APPROOT` (project root) are defined in `public/index.php`; `App`,
  `Controller`, and the autoloader use `APPROOT` instead of CWD-relative paths.
- `redirect()` no longer double-prefixes absolute URLs; `get_image()` resolves against `ROOTPATH`.
- `App::splitURL()` normalizes empty path segments.
- Removed dead commented code from `App`, `Model`, and `Database`.
- `Pager` no longer emits Bootstrap classes; it now emits plain Pico-friendly `<nav><ul>` markup.
- GD in `docker/web/Dockerfile` is now built with `--with-webp` (matching `Image.php`).

**Verification performed**

- `php -l` on all PHP files (PHP 8.3 container): clean.
- Built `docker/web/Dockerfile` and smoke-tested: `/`, `/home`, `/home/index` return 200;
  unknown route returns 404; static CSS returns 200.
- `.env` loader verified (value applied) and env-var precedence verified (real env wins).
- `scripts/migrate.php`, `scripts/seed.php`, and `scripts/db-status.php` run against MariaDB with
  empty migration/seeder sets.
- Composer path verified both ways: the built image runs `composer install` (generating
  `vendor/autoload.php`), and the app boots and serves routes when `vendor/` is absent (guarded
  include).

**Still open**

- §13.4 routing (explicit route map for CMS slugs).
- §10 P1 remainder: CSRF helper, clamping `limit`/`offset`, identifier whitelisting in query
  builders, minimal smoke-test scaffolding.
- Reintroduce an auth module as an explicitly optional, documented add-on if desired (recoverable
  from git history).

---

## 15. Deployment Model (applied)

Following the decision to run Docker in dev and a no-build shared host in production, the repo now
codifies a **code-vs-state** deployment model. Details are in `docs/DEPLOYMENT.md`.

- **Dev-only migrations.** The PHP migrator remains the dev authoring tool and never runs in
  production (the app does not read `schema_migrations`). Production receives SQL.
- **`make schema-dump`** exports structure-only (`mysqldump --no-data`) to `database/schema.sql`
  for the first deploy. `database/updates/*.sql` holds rare hand-written production deltas.
- **`make package`** builds a code-only release zip in `dist/`, excluding `.env`,
  `public/uploads/*`, `.git`, `docker/`, `docs/`, and editor files — so redeploys never clobber
  state.
- **`scripts/preflight.php` / `make preflight`** checks PHP version, required extensions,
  `putenv`, `.env` readability, core files, and `public/uploads/` writability.
- **`public/uploads/`** added with an `index.php` deny guard and a `.gitignore` that ignores its
  contents.
- **Docker/Apache parity.** `docker/apache/000-default.conf` now uses `AllowOverride All`, so the
  dev container exercises the same `public/.htaccess` rewrite as production instead of duplicating
  the rule in the vhost.
- **PHP floor pinned to 8.1** in `composer.json` (`config.platform`) and the runtime check, which
  also accommodates `nesbot/carbon` v3.
- **Content model direction:** stable JSON/key-value (`pages`, `settings`, `media`) so adding an
  editable field is a row, not a schema change (documented in `docs/DEPLOYMENT.md`).
- `PROJECT_PLAN.md` and this document supersede the original auth-focused plan.
- `public/.htaccess` also sets conservative static-asset caching and gzip (both `<IfModule>`
  guarded); `public/uploads/.htaccess` disables indexing and script execution.

---

## 16. Hardening & Cleanup Pass (applied)

- **Model safety** (`app/core/Model.php`): query keys are validated as bare SQL identifiers;
  `limit`/`offset` are cast to int; `order_type` is whitelisted and `order_column` validated; empty
  condition sets no longer produce invalid SQL. `insert`/`update` require `$allowedColumns` and
  intersect against it (no mass assignment).
- **CSRF** (`functions.php`): `csrf_token()`, `csrf_field()`, `csrf_verify()` (session token, accepts
  form field `_token` or `X-CSRF-Token` header).
- **Session hardening** (`Core\Session`): `httponly` + `samesite=Lax` + `secure` (when HTTPS) cookie
  params, `session.use_strict_mode=1`, and `regenerate()` for post-login.
- **Error handling**: `register_error_handling()` logs uncaught exceptions/fatals and shows a
  generic message in production; detailed only when `DEBUG_MODE`. `show()`/`dd()` are DEBUG_MODE-only.
- **Extension gate** reduced to `pdo_mysql`.
- **Removed dead code**: `Core\Image`, `Core\Pager`, and the unused legacy helpers listed in §4.
- **Hygiene**: generalized `robots.txt`; asset-directory guards return 403; `.editorconfig`; a GitHub
  Actions workflow lints on PHP 8.1/8.3 and runs preflight.

---

## 17. Optional CMS Module (auth + stable-JSON content)

A removable module for the brochure-site use case. It uses only convention-based placement, so it
needs no framework extension and is removed by deleting its files (list in `README.md`).

- **Auth:** `Login`/`Logout`/`Password` controllers, `Model\User`, session login with
  `Core\Session::regenerate()` (fixation protection), CSRF on every POST, and the generic
  `Core\RequiresLogin` guard trait.
- **Content:** `Model\Page`, `Model\Setting`, `Model\Media` over `pages`/`settings`/`media`
  migrations. Page bodies are JSON key/value blocks edited with a small Alpine component, so new
  editable blocks never change the schema.
- **Admin:** `Controller\Admin` covers dashboard, page CRUD, settings, and media upload with
  `getimagesize()` validation (no `gd`/`fileinfo` requirement). Images go to `public/uploads/`,
  which already denies script execution.
- **Public:** `Controller\Page` renders published pages; `__call()` turns the URL segment into the
  slug (`/page/about`), avoiding a dynamic route table.
- **Core additions:** `Model::count()`, `require_csrf()`, and `Core\RequiresLogin`.
