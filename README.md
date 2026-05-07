# Cocktail API

A REST API for managing cocktails, ingredients, categories, ratings, favorites, and user authentication.

This project was built to deepen my backend development skills with modern Laravel practices and to focus on scalable API architecture, authentication, validation, testing, and clean code organization.

---

# Features

- User registration and authentication with Laravel Sanctum
- Email verification
- CRUD operations for cocktails
- Cocktail ratings and comments
- Favorite cocktails
- Ingredient and category management for users with admin privileges
- Image upload support
- Cocktail filtering, searching, sorting, and pagination
- Role-based authorization with Laravel Policies
- API documentation with Scribe
- Static analysis with PHPStan/Larastan
- Automated feature testing

---

# Tech Stack

- PHP 8.3
- Laravel 12
- MySQL
- Laravel Sanctum
- PHPUnit
- PHPStan + Larastan
- Scribe API Documentation

---

# Architecture

The project follows a layered structure to keep responsibilities separated and maintainable.

## Main Architectural Concepts

### Actions

Business logic is extracted into dedicated action classes.

Examples:
- `CreateCocktailAction`
- `UpdateCocktailAction`
- `RateCocktailAction`

### ReadModels

Query logic is separated from controllers using dedicated query/read model classes.

The read models use a fluent interface to compose reusable query operations.

Example:

```php
$baseQuery = (new CocktailQuery())
    ->forScope($scope, $user)
    ->search($search)
    ->filter($filter)
    ->withRelations($relationsToBeLoaded)
    ->withStats()
    ->sort($sorting);
```

This allows:
- reusable filtering
- reusable sorting
- pagination
- composable query logic
- cleaner controllers
- separation of concerns

### Form Requests

Validation is fully handled through Laravel Form Requests.

### API Resources

Responses are transformed using Laravel API Resources to ensure consistent output formatting.

### Policies

Authorization is handled with Laravel Policies.

Examples:
- cocktail ownership checks
- admin-only category/ingredient management

---

# API Features

## Cocktails

Supports:
- filtering
- searching
- sorting
- pagination
- relationship includes

Example:

```http
GET /api/cocktails?search=mojito&include[]=ingredients&per_page=10
```

---

# Authentication

Authentication is implemented using Laravel Sanctum.

Protected routes use:

```php
auth:sanctum
```

---

# Testing

The project contains feature tests for:
- authentication
- cocktails
- categories
- ingredients
- ratings
- authorization
- image uploads

Run tests:

```bash
php artisan test
```

---

# Static Analysis

PHPStan and Larastan are used to improve type safety and code quality.

Run analysis:

```bash
./vendor/bin/phpstan analyse
```

---

# Installation

## Clone Repository

```bash
git clone https://github.com/IamDMC/cocktail-api.git
```

## Install Dependencies

```bash
composer install
```

## Environment Configuration

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Additional environment variables:

```env
FRONTEND_URL=http://localhost:3000
SCRIBE_AUTH_KEY=your_generated_token
```

## Run Migrations

```bash
php artisan migrate
```

## Run Seeders

```bash
php artisan db:seed
```

The database seeders create demo data for local API testing.

## Storage Link

Create the storage symlink for public image access:

```bash
php artisan storage:link
```

## Run Development Server

```bash
php artisan serve
```

## Queue Worker

If queue-based mail delivery is enabled, run:

```bash
php artisan queue:work
```

---

# API Documentation

Generate Scribe documentation:

```bash
php artisan scribe:generate
```

After generating the documentation, open:

```text
http://localhost/docs
```

The generated Postman collection can be accessed via the `View Postman Collection` button.

Import the collection into Postman to get a preconfigured API testing environment with:
- preconfigured endpoints
- request examples
- query parameters
- request body examples
- authentication structure

---

# Development Goals

This project was built to improve my skills in:
- backend architecture
- REST API design
- Laravel best practices
- authentication & authorization
- automated testing
- static analysis

---

# License

This project is open-source and available under the MIT License.
