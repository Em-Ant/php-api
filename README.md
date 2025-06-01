# PHP API with Slim Framework

A production-ready PHP API built with Slim Framework, featuring JWT authentication, dependency injection, logging, and Swagger documentation.

## Features

- **Slim Framework 4** - Modern PHP micro-framework
- **JWT Authentication** - Secure token-based authentication with JWKS support
- **Dependency Injection** - PHP-DI container for clean architecture
- **Logging** - Monolog for comprehensive logging
- **Swagger Documentation** - OpenAPI 3.0 specification with interactive UI
- **CORS Support** - Configurable cross-origin resource sharing
- **Environment Configuration** - dotenv for environment variables
- **PSR-4 Autoloading** - Modern PHP namespace structure

## Installation

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp example.env cfg.env
```

3. Configure your environment variables in `cfg.env`

4. Create logs directory:
```bash
mkdir logs
```

5. Start development server:
```bash
php -S localhost:8000 router.php
```

## API Endpoints

- `GET /health` - Health check endpoint
- `GET /api/v1/beers/random` - Get random beer
- `GET /api/v1/beers/{id}` - Get beer by id
- `GET /docs` - Swagger UI documentation
- `GET /docs/openapi.json` - OpenAPI specification

## Authentication

The API uses JWT tokens for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

For beer endpoints, the token must contain an 'admin' role in the `realm_access.roles` claim.

## Environment Variables

- `APP_ENV` - Application environment (development/production)
- `APP_DEBUG` - Enable debug mode (true/false)
- `APP_NAME` - Application name for logging
- `JWKS_URI` - JWT Key Set URI for token validation
- `JWT_ALGORITHM` - JWT algorithm (default: RS256)
- `LOG_LEVEL` - Logging level (debug/info/warning/error)
- `LOG_PATH` - Path to log file
- `CORS_ALLOWED_ORIGINS` - Comma-separated list of allowed CORS origins or *

## Architecture

The application follows a clean architecture pattern:

- **Controllers** - Handle HTTP requests and responses
- **Services** - Business logic and external API calls
- **Repositories** - Business logic and external API calls
- **Middleware** - Cross-cutting concerns (CORS, Authentication)
- **Config** - Application bootstrapping and dependency injection

## Documentation

Interactive API documentation is available at `/docs` when the server is running.

## License

MIT
