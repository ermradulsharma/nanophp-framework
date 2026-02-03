<p align="center">
  <img src="../../public/logo.png" alt="NanoPHP Logo" width="200">
</p>

# NanoPHP Framework Core

The engine behind the NanoPHP ecosystem. This package provides the core functionality, including dependency injection, routing, middleware processing, and integration with the Illuminate (Laravel) components.

## Core Components

- **Application Engine**: A PSR-11 compliant application container powered by PHP-DI.
- **Routing System**: Fast and flexible routing based on `nikic/fast-route`.
- **Middleware Infrastructure**: PSR-15 compliant middleware stack for request handling.
- **Database Layer**: Seamless integration with Laravel's Query Builder and Eloquent components.
- **Template Engine**: Native support for Blade via `illuminate/view`.
- **CLI Framework**: Robust console command system using `symfony/console`.

## Installation

As a developer using NanoPHP, you typically don't install this package directly. Instead, it is required by the [NanoPHP Skeleton](https://github.com/your-username/nanophp-skeleton).

However, if you wish to use the core in a custom project:

```bash
composer require nanophp/framework
```

## Architecture

NanoPHP follows a modular architecture where the framework is separated from the application skeleton.

- **Skeleton**: Contains specialized application logic, routes, and views.
- **Framework Core**: Manages service registration, bootstrapping, and low-level component orchestration.

## Features Documentation

### Routing

Register routes in your application's `routes/web.php`:

```php
use Nano\Framework\Facades\Route;

Route::get('/', function() {
    return 'Hello from NanoPHP!';
});
```

### Dependency Injection

Everything is registered in the DI container. You can use constructor injection in your controllers.

## License

The NanoPHP framework is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Laravel**: Special thanks to the Laravel team for the inspired architecture.
- **Illuminate**: This package integrates various [Illuminate](https://github.com/illuminate) components to provide a robust framework experience.
