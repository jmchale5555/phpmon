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
- `make prune-all` - run `docker system prune -a --volumes` (destructive)
