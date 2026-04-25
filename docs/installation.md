# Installation

## Framework

```bash
composer install
cp .env.example .env
```

Serve the built-in demo:

```bash
php -S 127.0.0.1:8080 -t public
```

## New Project

```bash
./bin/valhalla new project orders-service
cd orders-service
composer install
cp .env.example .env
php -S 127.0.0.1:8081 -t public
```

Valhalla expects:

- a `public/index.php` entrypoint
- a `routes/api.php` route file
- config files in `config/`
- app classes under `src/`
