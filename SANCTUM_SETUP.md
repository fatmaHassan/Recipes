# Laravel Sanctum Setup

## ✅ Completed Steps

1. ✅ Added `HasApiTokens` trait to `User` model
2. ✅ Created and ran migration for `personal_access_tokens` table
3. ✅ API routes configured with `auth:sanctum` middleware
4. ✅ Database table is ready

## ⚠️ IMPORTANT: Install Sanctum Package

**You need to install Sanctum when you have internet connection:**

```bash
composer require laravel/sanctum
```

**DO NOT manually edit composer.json** - use the `composer require` command above. This will:
- Add the package to `composer.json`
- Update `composer.lock` properly
- Install the package files

If you see the error "Required package laravel/sanctum is not present in the lock file", it means you need to run `composer require laravel/sanctum` instead of manually editing composer.json.

## Verification

After running `composer install`, verify Sanctum is working:

1. **Check if Sanctum is installed:**
   ```bash
   composer show laravel/sanctum
   ```

2. **Test API registration:**
   ```bash
   curl -X POST http://localhost:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{"name":"Test User","email":"test@example.com","password":"password","password_confirmation":"password"}'
   ```

3. **Test API login:**
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

## Current Status

- ✅ Database table created (`personal_access_tokens`)
- ✅ User model updated with `HasApiTokens` trait
- ✅ API routes configured
- ⏳ Package installation pending (requires internet)

## Next Steps

1. Run `composer install` when you have internet connectivity
2. Test the API endpoints using Postman or your mobile app
3. The API is ready to use once Sanctum package is installed

## Troubleshooting

If you get errors about Sanctum classes not found:
- Make sure you've run `composer install`
- Run `composer dump-autoload` to refresh autoloader
- Clear config cache: `php artisan config:clear`
