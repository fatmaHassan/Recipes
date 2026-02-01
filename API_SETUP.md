# API Setup Guide

This document explains how to set up and use the API endpoints for the Recipes application.

## Prerequisites

1. **Install Laravel Sanctum** (if not already installed):
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. **Configure Sanctum** (if needed):
   - Sanctum is already configured in the `User` model with `HasApiTokens` trait
   - API routes are registered in `bootstrap/app.php`
   - Routes are protected with `auth:sanctum` middleware

## API Base URL

- **Development**: `http://localhost:8000/api`
- **Production**: `https://your-domain.com/api`

## Authentication

### Register
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": { ... },
  "token": "1|xxxxxxxxxxxx"
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": { ... },
  "token": "2|xxxxxxxxxxxx"
}
```

### Using the Token

Include the token in the `Authorization` header for protected routes:
```http
Authorization: Bearer 2|xxxxxxxxxxxx
```

### Logout
```http
POST /api/logout
Authorization: Bearer 2|xxxxxxxxxxxx
```

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires auth)
- `GET /api/user` - Get authenticated user (requires auth)

### Dashboard
- `GET /api/dashboard` - Get dashboard stats (requires auth)

### Ingredients
- `GET /api/ingredients` - Get user's ingredients (requires auth)
- `POST /api/ingredients` - Add ingredient (requires auth)
- `DELETE /api/ingredients/{id}` - Delete ingredient (requires auth)
- `GET /api/ingredients/search?q={query}` - Search ingredients (requires auth)
- `POST /api/ingredients/check` - Check if ingredient exists (requires auth)

### Allergies
- `GET /api/allergies` - Get user's allergies (requires auth)
- `POST /api/allergies` - Add allergy (requires auth)
- `DELETE /api/allergies/{id}` - Delete allergy (requires auth)

### Recipes
- `GET /api/recipes/search?ingredients[]=chicken&ingredients[]=rice` - Search recipes (requires auth)
- `POST /api/recipes/search` - Search recipes (POST method) (requires auth)
- `GET /api/recipes/{id}` - Get recipe details (requires auth)
- `POST /api/recipes/save` - Save a recipe (requires auth)
- `POST /api/recipes/{recipeId}/favorite` - Toggle favorite status (requires auth)

### My Recipes
- `GET /api/my-recipes` - Get user's saved recipes (requires auth)

### Favorites
- `GET /api/favorites` - Get user's favorite recipes (requires auth)

### Profile
- `GET /api/profile` - Get user profile (requires auth)
- `PUT /api/profile` - Update user profile (requires auth)
- `DELETE /api/profile` - Delete user account (requires auth)

## Example Usage

### Using cURL

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password"}'

# Get ingredients (with token)
curl -X GET http://localhost:8000/api/ingredients \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Using JavaScript/Fetch

```javascript
// Login
const response = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'password'
  })
});

const data = await response.json();
const token = data.token;

// Use token for authenticated requests
const ingredientsResponse = await fetch('http://localhost:8000/api/ingredients', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

## CORS Configuration

If you're making requests from a mobile app or different domain, you may need to configure CORS in `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => ['*'], // Or specify your mobile app domain
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

## Testing

You can test the API endpoints using:
- Postman
- cURL
- Your mobile app
- Browser (for GET requests)

## Notes

- All protected routes require the `Authorization: Bearer {token}` header
- Tokens are created when users register or login
- Tokens are deleted when users logout
- The API returns JSON responses
- Error responses follow Laravel's standard format
