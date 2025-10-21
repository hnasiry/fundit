# Fundit - Banking API

A simplified banking API built with Laravel that demonstrates clean architecture patterns, token-based authentication, queued job processing, and automated documentation.

## Features

- Card-to-card transfers with balance validation and transactional integrity
- Token authentication powered by [Laravel Sanctum](https://laravel.com/docs/sanctum)
- Interactive Swagger UI documentation generated with [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger)
- Reporting endpoint that returns the top three users and their latest ten transactions
- Repository and service layers to encapsulate data access and business rules
- Queued notifications processed through [Laravel Horizon](https://laravel.com/docs/horizon)
- Comprehensive automated tests executed with Pest
- Docker Compose environment with PHP-FPM, Nginx, MySQL, and Redis

## Architecture Overview

```
app/
├── Exceptions/           # Domain specific exception types
├── Http/
│   ├── Controllers/      # Thin controllers delegating to services
│   ├── Requests/         # Form request validation
│   └── Resources/        # API response transformers
├── Jobs/                 # Queueable jobs (SMS notifications)
├── Models/               # Eloquent models and relationships
├── Repositories/         # Repository interfaces and Eloquent implementations
└── Services/
    ├── Notifications/    # SMS providers & manager (Strategy pattern)
    └── Transactions/     # Transfer logic and notification orchestration
```

## Requirements

- PHP 8.4+
- Composer
- Node.js & npm (for optional asset compilation)
- MySQL 8.x
- Redis 6.x (for queues and Horizon)
- Docker (optional, for containerized setup)

## Installation

1. Clone the repository and install PHP dependencies:

   ```bash
   git clone https://github.com/your-org/fundit.git
   cd fundit
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure `.env` with database and Redis credentials. The project defaults to `QUEUE_CONNECTION=redis` and `REDIS_CLIENT=predis` for Horizon support.

3. Run the migrations and seeders (creates a ready-to-use API testing user, demo accounts, and transactions):

   ```bash
   php artisan migrate --seed
   ```

4. Generate the OpenAPI specification and publish the Swagger UI assets:

   ```bash
   php artisan l5-swagger:generate
   ```

5. (Optional) Build frontend assets:

   ```bash
   npm install
   npm run build
   ```

## Seeded API Credentials

After seeding, you can authenticate immediately with the following user:

- **Email:** `apitester@example.com`
- **Password:** `Password123!`

Additional demo users, accounts, and cross-user transactions are created automatically for testing the reports and transfer flows.

## Running the Application

```bash
php artisan serve
```

Start Horizon to process queued jobs (required for transfer notifications):

```bash
php artisan horizon
```

The Horizon dashboard is available at `/horizon` (accessible in local/test environments or to emails listed in `HORIZON_VIEWER_EMAILS`).

## API Authentication Flow

1. **Register** – `POST /api/auth/register`
2. **Login** – `POST /api/auth/login`
3. **Use the returned bearer token** for all protected endpoints (`Authorization: Bearer <token>`)
4. **Logout** – `POST /api/auth/logout`

Example login request using the seeded user:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"apitester@example.com","password":"Password123!"}'
```

## Protected API Endpoints

All endpoints below require a valid Sanctum bearer token.

### Card-to-Card Transfer

`POST /api/transactions/transfer`

```bash
curl -X POST http://localhost:8000/api/transactions/transfer \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"source_card":"5555444433331111","destination_card":"5556000100000001","amount":75000}'
```

### Top Users Report

`GET /api/reports/top-users`

```bash
curl -X GET http://localhost:8000/api/reports/top-users \
  -H "Authorization: Bearer <token>"
```

### Swagger Documentation

Regenerate the docs whenever controller annotations change:

```bash
php artisan l5-swagger:generate
```

Open the interactive UI at: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

## Docker Setup

A complete environment is available via Docker Compose:

```bash
docker-compose up -d
```

After the containers start:

```bash
docker-compose exec app composer install
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan l5-swagger:generate
docker-compose exec app php artisan horizon
```

The API will be available at [http://localhost:8000](http://localhost:8000) and Horizon at [http://localhost:8000/horizon](http://localhost:8000/horizon).

## Running Tests

Execute the automated test suite using Pest:

```bash
php artisan test
```

## Extending SMS Providers

1. Create a new provider class that implements `App\Services\Notifications\Sms\SmsProviderInterface`.
2. Register the provider in `AppServiceProvider` by adding it to the `SmsManager` constructor array.
3. Add any required credentials to `config/sms.php` and `.env`.

## Troubleshooting

- Ensure Redis is running and reachable when starting Horizon.
- Run `php artisan optimize:clear` if you update configuration values that are cached.
- Regenerate Swagger docs after modifying controller or resource annotations.

