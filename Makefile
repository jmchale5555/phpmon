.PHONY: help up up-build down up-dev up-dev-build down-dev composer-install composer-update migrate seed db-status db-reset schema-dump preflight package prune-all

COMPOSE_BASE = docker compose
COMPOSE_DEV = docker compose -f docker-compose.yml -f docker-compose.dev.yml
PACKAGE_DIR = dist/phpmon
PACKAGE_NAME = phpmon-$(shell date +%Y%m%d)

help:
	@printf "Targets:\n"
	@printf "  make up            - compose up (attached)\n"
	@printf "  make up-build      - compose up --build (attached)\n"
	@printf "  make down          - compose down\n"
	@printf "  make up-dev        - compose up with dev override (attached)\n"
	@printf "  make up-dev-build  - compose up --build with dev override (attached)\n"
	@printf "  make down-dev      - compose down with dev override\n"
	@printf "  make composer-install - run composer install in dev container\n"
	@printf "  make composer-update  - run composer update in dev container\n"
	@printf "  make migrate       - run PHP migrations in dev container\n"
	@printf "  make seed          - run PHP seeders in dev container\n"
	@printf "  make db-status     - print DB migration/seeder status\n"
	@printf "  make db-reset      - drop/recreate DB, then migrate + seed (dev)\n"
	@printf "  make schema-dump   - export dev schema to database/schema.sql (deploy artifact)\n"
	@printf "  make preflight     - run the environment preflight checks in dev container\n"
	@printf "  make package       - build a code-only release zip in dist/ (excludes state)\n"
	@printf "  make prune-all     - docker system prune -a --volumes (destructive)\n"

up:
	$(COMPOSE_BASE) up

up-build:
	$(COMPOSE_BASE) up --build

down:
	$(COMPOSE_BASE) down

up-dev:
	$(COMPOSE_DEV) up

up-dev-build:
	$(COMPOSE_DEV) up --build

down-dev:
	$(COMPOSE_DEV) down

composer-install:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web composer install

composer-update:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web composer update

migrate:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web php scripts/migrate.php

seed:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web php scripts/seed.php

db-status:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web php scripts/db-status.php

db-reset:
	$(COMPOSE_DEV) exec db mariadb -uroot -p"$${DB_ROOT_PASS:-root}" -e "DROP DATABASE IF EXISTS \`$${DB_NAME:-phpmon}\`; CREATE DATABASE \`$${DB_NAME:-phpmon}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \`$${DB_NAME:-phpmon}\`.* TO '$${DB_USER:-phpmon}'@'%'; FLUSH PRIVILEGES;"
	$(MAKE) migrate
	$(MAKE) seed

# Export structure only (no data) from the dev database. This is the artifact
# you import into a shared host for the first deploy.
schema-dump:
	$(COMPOSE_DEV) exec -T db sh -c 'mariadb-dump -uroot -p"$${MARIADB_ROOT_PASSWORD:-root}" --no-data --routines --triggers --skip-comments "$${MARIADB_DATABASE:-phpmon}"' > database/schema.sql
	@echo "wrote database/schema.sql"

preflight:
	$(COMPOSE_DEV) run --rm --no-deps --user "$$(id -u):$$(id -g)" web php scripts/preflight.php

# Build a code-only release. State (.env, database, uploads) is intentionally
# excluded and must be preserved on the host across redeploys.
package:
	@rm -rf dist
	@mkdir -p $(PACKAGE_DIR)
	@rsync -a \
		--exclude='.git' \
		--exclude='.gitignore' \
		--exclude='.dockerignore' \
		--exclude='.env' \
		--exclude='.vscode' \
		--exclude='AGENTS.md' \
		--exclude='docker' \
		--exclude='docker-compose.yml' \
		--exclude='docker-compose.dev.yml' \
		--exclude='docs' \
		--exclude='Makefile' \
		--exclude='dist' \
		--exclude='*.swp' \
		--include='public/uploads/' \
		--include='public/uploads/index.php' \
		--include='public/uploads/.htaccess' \
		--exclude='public/uploads/*' \
		./ $(PACKAGE_DIR)/
	@cd dist && zip -qr $(PACKAGE_NAME).zip phpmon
	@echo "created dist/$(PACKAGE_NAME).zip"
	@echo "  web root  <- contents of public/"
	@echo "  one above <- app/ database/ scripts/ vendor/ .env.example"

prune-all:
	docker system prune -a --volumes
