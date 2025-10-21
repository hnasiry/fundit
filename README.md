# Fundit - Banking API

A simplified banking system API built with Laravel that demonstrates clean architecture patterns, a multi-provider SMS notification strategy, queued jobs, reporting endpoints, automated testing, and Docker-based local development.

## Features

- Card-to-card transfers with balance validation and transactional integrity
- SMS notifications for senders and receivers using a pluggable provider strategy
- Reporting endpoint that returns the top three users and their latest ten transactions
- Repository and service layers to encapsulate data access and business rules
- Asynchronous job dispatching via Laravel queues
- Comprehensive unit and feature test coverage executed with Pest
- Docker Compose environment with PHP-FPM, Nginx, and MySQL
- GitHub Actions workflow for automated CI testing

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

Business logic lives inside services and repositories while controllers remain thin. SMS notifications use the Strategy pattern so new providers can be introduced by implementing `SmsProviderInterface` and registering the class.

## Getting Started

### Requirements

- PHP 8.2+ (or Docker)
- Composer
- Node.js & npm (for optional asset compilation)
- MySQL 8.x

### Installation

1. Clone the repository and install PHP dependencies:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure your `.env` file with database credentials and SMS provider settings.

3. Run migrations and seeders as needed:

   ```bash
   php artisan migrate
   ```

4. (Optional) Build frontend assets:

   ```bash
   npm install
   npm run build
   ```

### Docker Setup

A complete environment is available via Docker Compose:

```bash
docker-compose up -d
```

After the containers start:

1. Install dependencies inside the application container:

   ```bash
   docker-compose exec app composer install
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   ```

2. The API will be available at [http://localhost:8000](http://localhost:8000).

### Running Tests

Execute the automated test suite (unit + feature) using Pest:

```bash
php artisan test
```

## API Reference

### Card-to-Card Transfer

`POST /api/transactions/transfer`

Request body:

```json
{
  "source_card": "6037991234567890",
  "destination_card": "6274129876543210",
  "amount": 150000
}
```

Successful response:

```json
{
  "success": true,
  "reference_number": "TRX-20250101-ABC123"
}
```

### Top Users Report

`GET /api/reports/top-users`

Response body:

```json
{
  "top_users": [
    {
      "user_id": "1",
      "name": "Ali",
      "total_transactions": 45,
      "latest_transactions": [
        {
          "reference_number": "TRX-20251013-0001",
          "source_card": "603799****7890",
          "destination_card": "627412****3210",
          "amount": 150000,
          "created_at": "2025-10-13T12:00:00"
        }
      ]
    }
  ]
}
```

Card numbers are masked in reports to protect sensitive data.

## GitHub Actions CI

The repository includes a workflow at `.github/workflows/tests.yml` that installs dependencies and runs the test suite on every push and pull request to ensure continuous integration.

## Extending SMS Providers

1. Create a new provider class that implements `App\Services\Notifications\Sms\SmsProviderInterface`.
2. Register the provider in `AppServiceProvider` by adding it to the `SmsManager` constructor array.
3. Add any required credentials to `config/sms.php` and `.env`.

