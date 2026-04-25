# basic-php-mvc
# basic-php-mvc

## Docker commands

Run these from the project root:

- `make up` - run base compose stack in attached mode
- `make up-build` - rebuild and run base stack in attached mode
- `make down` - stop base stack
- `make up-dev` - run dev stack (bind mount + UID/GID mapping) in attached mode
- `make up-dev-build` - rebuild and run dev stack in attached mode
- `make down-dev` - stop dev stack
- `make composer-install` - run `composer install` in dev container (writes to host via bind mount)
- `make composer-update` - run `composer update` in dev container (writes to host via bind mount)
- `make prune-all` - run `docker system prune -a --volumes` (destructive)

## Composer

- `vendor/` is intentionally gitignored.
- `composer.json` keeps direct dependencies only; packages under `vendor/symfony`, `vendor/psr`, etc. are transitive dependencies of direct packages (for example `nesbot/carbon`).
- The web image runs `composer install` during build so image-copy mode is self-contained.
- In dev bind-mount mode, run `make composer-install` after dependency changes so `vendor/` is present on your host-mounted project.
