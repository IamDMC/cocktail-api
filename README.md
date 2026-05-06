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
- Ingredient and category management
- Image upload support
- Filtering, searching, sorting, and pagination
- Role-based authorization with policies
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

This allows:
- reusable filtering
- sorting
- pagination
- cleaner controllers

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

## Run Migrations

```bash
php artisan migrate
```

## Run Seeders

```bash
php artisan db:seed
```

---

# API Documentation

Generate Scribe documentation:

```bash
php artisan scribe:generate
```

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
