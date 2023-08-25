# Conscribo API

This project is a modern, read-only implementation for the Consribo API.
It handles authentication, session management, query construction and performing proper API calls.

## Installation

Simply require this package via Composer:

```bash
composer require gumbo-millennium/conscribo-api
```

## Configuration

The API doesn't publish it's own configuration, since it reads the `services.php` config file.

Therefore, you should add the following properties to your `services.php` file:

- `conscribo.account` - The name of the account you want to use (`https://secure.conscribo.nl/<account>`)
- `conscribo.username` - The username of the account you want to use
- `conscribo.password` - The password of the account you want to use
- `conscribo.entities.user` - The name of the entity ("Relatie") that represents a user
- `conscribo.entities.group` - The name of the entity ("Relatie") that represents a group

To validate your configuration, you may run the `conscribo:validate` command via Laravel's Artisan CLI.

## Usage

The Conscribo API is available via Contract implementation _and_ as a Facade. Both implementations are interchangeable and
share a single instance of the API (to maintain session state).

### Usage with a Facade

If you're using discovery, Laravel will mount the `ConscriboFacade` as `Conscribo` in the root namespace.

```php
// When not using discovery:
use GumboMillennium\ConscriboApi\ConscriboFacade as Conscribo;
// When using discovery:
use Conscribo;

// Get all users
Conscribo::users()->all();

// Get a single user
Conscribo::users()->find(1);
```

### Usage with a Contract

If you instead want to use a contract, you can use dependency injection:

```php
use Gumbo\ConscriboApi\Contracts\ConscriboApiContract;

class ConscriboAccountController extends Controller
{
    public function index(ConscriboApiContract $api): JsonResponse
    {
        return response()->json($api->users()->find(1));
    }
}
```
