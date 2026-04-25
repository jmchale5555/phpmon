.PHONY: help up up-build down up-dev up-dev-build down-dev prune-all

COMPOSE_BASE = docker compose
COMPOSE_DEV = docker compose -f docker-compose.yml -f docker-compose.dev.yml

help:
	@printf "Targets:\n"
	@printf "  make up            - compose up (attached)\n"
	@printf "  make up-build      - compose up --build (attached)\n"
	@printf "  make down          - compose down\n"
	@printf "  make up-dev        - compose up with dev override (attached)\n"
	@printf "  make up-dev-build  - compose up --build with dev override (attached)\n"
	@printf "  make down-dev      - compose down with dev override\n"
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

prune-all:
	docker system prune -a --volumes
