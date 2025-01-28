# Translation Management Service

## Setup

1. Clone the repo.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure database.
4. Run migrations: `php artisan migrate`.
4. Run seeder: `php artisan db:seed`.
5. Generate JWT key: `php artisan jwt:secret`.
6. Start server: `php artisan serve` or use Sail.

## Features

- Token-based authentication.
- CRUD operations for translations with tags.
- High-performance JSON export endpoint.

## Testing

Run `php artisan test` to execute tests. For performance tests, ensure the database is seeded with 100k+ records using `php artisan translations:generate`.