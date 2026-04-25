# CLI

Valhalla includes a Composer-installed CLI at `bin/valhalla`.

## Commands

| Command | Description |
| --- | --- |
| `valhalla new project NAME` | Create a new API project |
| `valhalla make:controller NAME` | Generate a controller |
| `valhalla make:middleware NAME` | Generate a middleware |
| `valhalla make:service NAME` | Generate a service helper |
| `valhalla install` | Run Composer install |
| `valhalla auth:generate [id] [name]` | Generate a JWT |
| `valhalla routes:list` | Print registered routes |
| `valhalla agent:install NAME [port]` | Register a local agent |
| `valhalla agent:start NAME` | Start an agent |
| `valhalla agent:stop NAME` | Stop an agent |
| `valhalla agent:call NAME TASK` | Call an agent |
| `valhalla agent:list` | List installed agents |

## Global Usage

When `valhalla/framework` is installed globally through Composer, the CLI runs from anywhere and acts on the current working directory:

```bash
composer global require valhalla/framework
export PATH="$PATH:$HOME/.composer/vendor/bin:$HOME/.config/composer/vendor/bin"
```

Create a new project from any path:

```bash
cd ~/Projects
valhalla new project orders-service
```

Install dependencies immediately during scaffolding:

```bash
valhalla new project orders-service --install
```

## Scaffolding Flow

```bash
./bin/valhalla make:controller OrdersController
./bin/valhalla make:middleware InternalAuth
./bin/valhalla make:service BillingService
```
