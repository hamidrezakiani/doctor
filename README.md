<p align="center">
    <h1 align="center">Doctor Backend</h1>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4" alt="PHP 8.3+">
    <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20" alt="Laravel 11">
    <img src="https://img.shields.io/badge/PostgreSQL-17-4169E1" alt="PostgreSQL 17">
    <img src="https://img.shields.io/badge/Docker-Compose-2496ED" alt="Docker Compose">
</p>

## About The Project

Doctor Backend is a REST API for product synchronization and order management.

The project was implemented as a backend technical assessment with a focus on:

* Domain-oriented architecture
* Separation of Product and Order domains
* Repository pattern
* DTOs
* Strategy pattern for shipping costs
* Action-based business logic
* Transactional order creation
* Atomic stock management
* Scheduled order delivery
* Feature testing
* Dockerized development environment

The application does not include a frontend because the assessment requires a REST API only.

---

## Tech Stack

* PHP 8.3+
* Laravel 11.x
* PostgreSQL 17
* Docker Compose
* Nginx
* PHP-FPM
* Redis 7
* Elasticsearch 8
* PHPUnit
* Laravel Pint

---

## Requirements

Only the following are required on the host machine:

* Docker
* Docker Compose

PHP, PostgreSQL, Redis, Elasticsearch, and Nginx do not need to be installed locally.

---

# Installation

## 1. Clone the repository

```bash
git clone git@github.com:hamidrezakiani/doctor.git
cd doctor
```

## 2. Configure environment

Copy the example environment file:

```bash
cp .env.example .env
```

The application uses PostgreSQL inside Docker.

The relevant database configuration is:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=doctor
DB_USERNAME=doctor
DB_PASSWORD=doctor
```

Redis is available inside the Docker network through:

```env
REDIS_HOST=redis
REDIS_PORT=6379
```

## 3. Build and start the containers

```bash
docker compose up -d --build
```

Check the containers:

```bash
docker compose ps
```

## 4. Install Composer dependencies

```bash
docker compose exec app composer install
```

## 5. Generate the application key

```bash
docker compose exec app php artisan key:generate
```

## 6. Run database migrations

```bash
docker compose exec app php artisan migrate
```

## 7. Synchronize products

```bash
docker compose exec app php artisan products:sync
```

The command fetches products from:

```text
https://dummyjson.com/products?limit=10&skip=10&select=title,price,stock
```

and stores the product information in PostgreSQL.

The synchronized fields are:

* external product ID
* title
* price
* stock

The application is then available at:

```text
http://localhost:8000
```

## 8. Static Analysis

Larastan is used to perform static analysis on the Laravel application.

It helps detect potential issues such as:

- Invalid method or property calls
- Incorrect parameter and return types
- Possible null values
- Laravel-specific type and Eloquent issues

Run static analysis with:

```bash
docker compose exec app composer analyse
```

---

# Docker Services

The project runs the following services through Docker Compose:

| Service         | Description                   |   Port |
| --------------- | ----------------------------- | -----: |
| `app`           | Laravel application / PHP-FPM | `9000` |
| `nginx`         | HTTP server                   | `8000` |
| `postgres`      | PostgreSQL database           | `5432` |
| `redis`         | Redis                         | `6379` |
| `elasticsearch` | Elasticsearch                 | `9200` |
| `scheduler`     | Laravel scheduler worker      |      - |

The main API entry point is:

```text
http://localhost:8000
```

---

# API

## Create Order

```http
POST /api/orders
```

Example request:

```json
{
    "product_id": 1,
    "quantity": 2,
    "shipping_method": "post"
}
```

### Shipping Methods

| Method   |   Cost |
| -------- | -----: |
| `post`   | 30,000 |
| `tipax`  | 40,000 |
| `pickup` |      0 |

The order subtotal is calculated as:

```text
subtotal = product price × quantity
```

The final total is:

```text
total = subtotal + shipping cost
```

The calculated values are stored with the order.

### Successful Response

```http
201 Created
```

Example:

```json
{
    "data": {
        "id": 1,
        "status": "paid",
        "shipping_method": "post",
        "subtotal": 200000,
        "shipping_cost": 30000,
        "total": 230000,
        "created_at": "2026-09-28T00:00:00.000000Z"
    }
}
```

---

## List Orders

```http
GET /api/orders
```

### Pagination

```http
GET /api/orders?per_page=15
```

### Filter by Status

```http
GET /api/orders?status=shipped
```

### Pagination + Filter

```http
GET /api/orders?status=shipped&per_page=10
```

Supported statuses:

```text
paid
shipped
delivered
```

The endpoint returns a Laravel paginated resource response.

---

## Ship Order

An order can transition from:

```text
paid → shipped
```

Endpoint:

```http
PATCH /api/orders/{order}/ship
```

Example:

```http
PATCH /api/orders/1/ship
```

When an order is shipped, the application records the current time in:

```text
shipped_at
```

Invalid status transitions are rejected with:

```http
409 Conflict
```

The complete lifecycle is:

```text
paid
  ↓
shipped
  ↓
delivered
```

---

# Scheduled Delivery

Orders that have been in the `shipped` state for more than 24 hours are automatically moved to `delivered`.

The command can be executed manually:

```bash
docker compose exec app php artisan orders:deliver-expired
```

The command searches for orders satisfying:

```text
status = shipped
AND
shipped_at <= now() - 24 hours
```

and changes their status to:

```text
delivered
```

## Laravel Scheduler

The command is registered in Laravel's scheduler:

```php
Schedule::command('orders:deliver-expired')
    ->hourly();
```

A dedicated Docker service runs:

```bash
php artisan schedule:work
```

This allows scheduled processing to run independently of the HTTP application.

---

# Architecture

The application follows a domain-oriented architecture.

```text
app/
├── Domains/
│   ├── Product/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Repositories/
│   │   │   ├── Contracts/
│   │   │   └── Eloquent/
│   │   └── Services/
│   │
│   └── Order/
│       ├── Actions/
│       ├── DTOs/
│       ├── Enums/
│       ├── Repositories/
│       └── Strategies/
│
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
└── Models/
    ├── Product.php
    ├── Order.php
    └── OrderItem.php
```

The Product and Order domains have separate application logic and persistence abstractions.

The HTTP layer is responsible for:

* request validation
* DTO creation
* HTTP responses
* API resources

The application/domain layer is responsible for:

* business rules
* transactions
* inventory management
* order lifecycle
* shipping cost calculation

---

# Actions

Business use cases are implemented as dedicated Actions.

Current examples include:

```text
SyncProductsAction
CreateOrderAction
ShipOrderAction
DeliverExpiredOrdersAction
ListOrdersAction
```

Controllers delegate business operations to Actions rather than containing the complete business logic themselves.

The general flow for order creation is:

```text
HTTP Request
      ↓
Form Request
      ↓
DTO
      ↓
CreateOrderAction
      ↓
Repository / Strategy
      ↓
Database
```

---

# DTOs

The application uses dedicated DTOs instead of passing raw request arrays into the application layer.

For example:

```text
CreateOrderData
```

contains:

```php
productId
quantity
shippingMethod
```

The DTO provides a typed contract between the HTTP layer and the application layer.

This keeps Laravel-specific HTTP concerns outside the core use case.

---

# Repository Pattern

Persistence is abstracted through repository interfaces.

For example:

```text
ProductRepositoryInterface
        ↓
EloquentProductRepository
```

The interface is bound to the concrete implementation through a Service Provider.

This keeps application logic independent from the concrete persistence implementation.

The same pattern is used for order-related repositories.

---

# Shipping Strategy

Shipping cost calculation uses the Strategy pattern.

The common contract is:

```php
interface ShippingCostStrategy
{
    public function supports(ShippingMethod $method): bool;

    public function calculate(): int;
}
```

Current strategies are:

```text
PostShippingStrategy
TipaxShippingStrategy
PickupShippingStrategy
```

A resolver selects the appropriate strategy based on the requested shipping method.

This means adding another shipping method does not require modifying the core order creation logic.

For example, a future shipping method can be implemented as another strategy:

```text
NewShippingStrategy
```

without adding another conditional branch to `CreateOrderAction`.

---

# Stock Management

Inventory is one of the critical parts of order creation.

A naive implementation would read the product first and then update the stock:

```php
$product = Product::find($productId);

if ($product->stock >= $quantity) {
    $product->stock -= $quantity;
    $product->save();
}
```

This read-modify-write approach can produce a race condition when multiple requests try to purchase the same final stock.

Instead, the application performs a conditional atomic database update:

```php
$stockUpdated = Product::query()
    ->whereKey($data->productId)
    ->where('stock', '>=', $data->quantity)
    ->decrement('stock', $data->quantity);
```

Conceptually, the database performs:

```sql
UPDATE products
SET stock = stock - ?
WHERE id = ?
  AND stock >= ?;
```

The affected row count is checked.

If one row is affected:

```text
stockUpdated = 1
```

the stock reservation succeeded.

If no rows are affected:

```text
stockUpdated = 0
```

the requested quantity is not available and the application throws an insufficient-stock exception.

The request then returns:

```http
409 Conflict
```

This makes the stock check and decrement a single database operation.

Order creation is also wrapped in a database transaction so that the stock change and order creation remain atomic.

If order creation fails, the stock decrement is rolled back.

---

# Database Design

## Products

The `products` table contains:

```text
id
external_id
title
price
stock
created_at
updated_at
```

`external_id` is unique because it identifies the product in the external DummyJSON API.

The local `id` remains the database primary key.

---

## Orders

The `orders` table contains:

```text
id
status
shipping_method
subtotal
shipping_cost
total
shipped_at
created_at
updated_at
```

The `status` column is indexed because orders are filtered by status.

The following composite index is used for scheduled delivery:

```text
(status, shipped_at)
```

This supports the query used to find shipped orders older than 24 hours.

---

## Order Items

The `order_items` table contains:

```text
id
order_id
product_id
quantity
unit_price
total
created_at
updated_at
```

Foreign keys maintain referential integrity between orders, products, and order items.

`unit_price` is stored on the order item so that the historical price used during order creation is preserved even if the product price changes later.

---

# Money Representation

Money values are stored as integers.

For example:

```text
30,000 تومان
```

is stored as:

```text
30000
```

This avoids floating-point precision problems when calculating monetary values.

Product prices returned by DummyJSON are converted into the application's integer monetary representation during synchronization.

---

# Validation

Order creation uses a dedicated Form Request.

The following fields are validated:

```text
product_id
quantity
shipping_method
```

`quantity` must be at least:

```text
1
```

`shipping_method` must be one of the supported `ShippingMethod` enum values.

The order listing endpoint validates:

```text
status
per_page
```

---

# Error Handling

## Insufficient Stock

When the requested quantity is not available:

```http
409 Conflict
```

Example:

```json
{
    "message": "Insufficient product stock."
}
```

## Invalid Order Transition

Invalid order status transitions return:

```http
409 Conflict
```

## Validation Errors

Invalid request data returns Laravel's standard:

```http
422 Unprocessable Entity
```

---

# Testing

The complete test suite can be executed inside Docker:

```bash
docker compose exec app php artisan test
```

A specific test can be executed with:

```bash
docker compose exec app php artisan test --filter=DeliverExpiredOrdersTest
```

The Feature tests cover:

* successful order creation
* insufficient stock
* order listing
* status filtering
* pagination
* automatic delivery after 24 hours
* order shipping lifecycle

Tests verify API responses and database state.

Examples of assertions include:

```php
$this->assertDatabaseHas(...);

$response->assertJsonStructure(...);

$response->assertStatus(...);
```

---

# Code Quality

The project uses Laravel Pint for code formatting.

Check formatting:

```bash
docker compose exec app ./vendor/bin/pint --test
```

Automatically fix formatting:

```bash
docker compose exec app ./vendor/bin/pint
```

New PHP files use:

```php
declare(strict_types=1);
```

The code follows PSR-12-compatible formatting conventions.

---

# Useful Artisan Commands

## Product Synchronization

```bash
docker compose exec app php artisan products:sync
```

## Deliver Expired Orders

```bash
docker compose exec app php artisan orders:deliver-expired
```

## Migrations

```bash
docker compose exec app php artisan migrate
```

## Migration Status

```bash
docker compose exec app php artisan migrate:status
```

## Tests

```bash
docker compose exec app php artisan test
```

## Routes

```bash
docker compose exec app php artisan route:list
```

## Tinker

```bash
docker compose exec app php artisan tinker
```

---

# Useful Docker Commands

## Start

```bash
docker compose up -d
```

## Start and Rebuild

```bash
docker compose up -d --build
```

## Stop

```bash
docker compose down
```

## Container Status

```bash
docker compose ps
```

## Application Logs

```bash
docker compose logs -f app
```

## Nginx Logs

```bash
docker compose logs -f nginx
```

## Scheduler Logs

```bash
docker compose logs -f scheduler
```

## PostgreSQL Logs

```bash
docker compose logs -f postgres
```

---

# Technical Decisions

## PostgreSQL

PostgreSQL is used as the primary relational database required by the assessment.

It provides the transactional and atomic update semantics required for order creation and inventory management.

## Integer Money

Monetary values are represented as integers to avoid floating-point precision problems.

## External Product ID

The external product identifier is stored separately as `external_id`.

This keeps the external API identifier separate from the application's local database primary key.

## Repository Pattern

Repositories isolate persistence logic from the application layer and provide explicit contracts for database operations.

## DTOs

DTOs provide typed input contracts between HTTP requests and application Actions.

## Strategy Pattern

Shipping costs are implemented as independent strategies.

This keeps the order creation logic independent from individual shipping implementations.

## Actions

Actions represent individual business use cases and keep controllers thin.

## Transactions

Order creation runs inside a database transaction.

The stock decrement and order creation therefore succeed or fail together.

## Atomic Stock Update

The stock availability check and decrement are performed by a single conditional database update.

This avoids a vulnerable read-modify-write sequence and prevents the application from accepting an order when the requested stock is no longer available.

## Scheduled Processing

The order delivery command is registered with Laravel's scheduler and runs hourly through a dedicated Docker scheduler service.

---

# Development Workflow

The project is developed through incremental Git commits rather than a single final commit.

Example commit structure:

```text
feat: add product synchronization
feat: add order creation and listing
feat: add order shipping lifecycle
feat: add scheduled order delivery
test: add order feature tests
```

This keeps the development history organized around meaningful changes.

---

# Complete Setup

For a fresh environment, the complete setup is:

```bash
git clone <repository-url>

cd doctor

cp .env.example .env

docker compose up -d --build

docker compose exec app composer install

docker compose exec app php artisan key:generate

docker compose exec app php artisan migrate

docker compose exec app php artisan products:sync

docker compose exec app php artisan test
```

After these steps, the API is available at:

```text
http://localhost:8000
```

---

# Project Scope

This project was developed specifically as a backend technical assessment.

The implementation focuses on:

* REST API design
* Business logic separation
* Database integrity
* Inventory management
* Order lifecycle
* Scheduled processing
* Automated testing
* Dockerized execution
* Maintainable architecture

There is no frontend/UI because it is outside the scope of the assessment.

---

# License

This project was developed as part of a technical assessment.
