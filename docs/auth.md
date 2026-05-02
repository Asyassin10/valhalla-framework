# Auth

Valhalla supports JWT bearer auth and API tokens.

## Generate a Token

```bash
./bin/valhalla auth:generate 1 "Jane Doe"
```

## Protect a Route

```php
use Valhalla\Framework\Auth\Auth;
use Valhalla\Framework\Core\Response;
use Valhalla\Framework\Facades\Route;
use Valhalla\Framework\Middleware\AuthMiddleware;

Route::get('/secure', fn () => Response::json([
    'authenticated' => true,
    'user' => Auth::user(),
]), [AuthMiddleware::class]);
```

## API Tokens

Add static service tokens in `config/auth.php`:

```php
'api_tokens' => [
    'internal-token' => [
        'id' => 'service.orders',
        'name' => 'Orders Service',
        'roles' => ['service'],
    ],
],
```

Then send the token in `X-API-Token`.
