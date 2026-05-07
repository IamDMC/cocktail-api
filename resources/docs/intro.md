# Cocktail API

The Cocktail API allows authenticated users to manage cocktails, ingredients, categories, ratings and favorites.

This API is built with Laravel 12 and uses token-based authentication via Laravel Sanctum.

All responses are returned in JSON format.

## Authentication

Authenticate via Bearer token.

After login, include the token in the Authorization header:

```http
Authorization: Bearer YOUR_TOKEN
```

Most endpoints require:
- an authenticated user
- a verified email address

## Response Format

Successful responses return JSON resources.

Example:

```json
{
  "data": {
    "id": 1,
    "name": "Mojito"
  }
}
```

Validation errors return HTTP 422 responses.

## Including Relationships

Cocktail endpoints support eager loading via the `include[]` query parameter.

Example:

```http
GET /api/cocktails?include[]=categories&include[]=ingredients
```

Available includes:
- user
- categories
- ingredients
- steps
- ratings.user
- favoredBy
- image

## Filtering

Cocktail collection endpoints support filtering via the `filter[]` query parameter.

Example:

```http
GET /api/cocktails?filter[0][name]=categories&filter[0][values][]=1
```

Available filters:
- categories
- ingredients

## Sorting

Cocktail collection endpoints support sorting.

Example:

```http
GET /api/cocktails?sorting[0][attribute]=name&sorting[0][direction]=asc
```

Available sorting attributes:
- name
- created_at

Available sorting directions:
- asc
- desc

## Pagination

Paginated responses support the `per_page` parameter.

Example:

```http
GET /api/cocktails?per_page=20
```

Some endpoints also support limiting results:

```http
GET /api/cocktails?limit=5
```

## File Uploads

Cocktail create and update endpoints support image uploads using `multipart/form-data`.

Example fields:

```text
name: Mojito
image: cocktail.png
```

## Authorization

Some endpoints require administrator privileges.

Creating, updating and deleting:
- categories
- ingredients

is restricted to admin users only.

Cocktails can only be updated or deleted by their owner.

## HTTP Status Codes

| Status Code | Meaning |
|---|---|
| 200 | Successful request |
| 201 | Resource created |
| 204 | Resource deleted |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Resource not found |
| 422 | Validation error |

## Development Notes

This project is actively developed and endpoints may evolve over time.
