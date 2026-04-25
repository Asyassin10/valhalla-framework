<<<<<<< HEAD
# valhalla-framework
=======
# Valhalla

Valhalla is a microservices-only PHP 8.2+ framework for lightweight JSON APIs, service-to-service calls, and local AI-agent style workers over JSON/TCP.

## Quick Start

```bash
composer install
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Then open `http://127.0.0.1:8080/health`.

## CLI

```bash
./bin/valhalla routes:list
./bin/valhalla auth:generate 1 "Jane Doe"
./bin/valhalla agent:install summarizer 9501
./bin/valhalla agent:start summarizer
./bin/valhalla agent:call summarizer summarize
```

### Global Install

Once `valhalla/framework` is published on Packagist, users can install the CLI globally:

```bash
composer global require valhalla/framework
```

Then make sure Composer's global bin directory is on `PATH`, for example:

```bash
export PATH="$PATH:$HOME/.composer/vendor/bin:$HOME/.config/composer/vendor/bin"
```

After that:

```bash
valhalla new project orders-service
cd orders-service
composer install
php -S 127.0.0.1:8080 -t public
```

## Project Layout

- `src/` framework code
- `config/` framework defaults
- `public/` framework demo entrypoint
- `routes/` demo routes
- `examples/basic-service/` runnable sample service
- `tests/` PHPUnit coverage
- `docs/` guides
- `website/` static marketing site

## Docs

- [Installation](./docs/installation.md)
- [Routing](./docs/routing.md)
- [Auth](./docs/auth.md)
- [Agents](./docs/agents.md)
- [CLI](./docs/cli.md)
- [Testing](./docs/testing.md)
- [Services](./docs/services.md)
>>>>>>> acaecb9 (Initial commit v1.0)
