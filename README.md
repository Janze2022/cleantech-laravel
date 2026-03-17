## CleanTech Laravel

This is the CleanTech booking platform built on Laravel 12.

## Local setup

1. Install PHP and Composer dependencies with `composer install`.
2. Copy `.env.example` to `.env`.
3. Set your database, mail, and app URL values.
4. Generate the key with `php artisan key:generate`.
5. Run migrations with `php artisan migrate --force`.
6. Create the storage symlink with `php artisan storage:link`.
7. Start the app with `php artisan serve`.

## Laravel Cloud deploy checklist

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL` to your real Laravel Cloud URL, not a local IP address
- Set the correct MySQL credentials for Cloud
- Set a valid `APP_KEY`
- Run `php artisan migrate --force` during deploy
- Run `php artisan storage:link` if your deployment image does not already create it
- Clear cached config/routes after env changes with:

```bash
php artisan optimize:clear
```

## Notes

- Provider profile images are served through app routes, so they do not depend on direct public `storage` URLs.
- Customer profile images are saved under `public/uploads/customers`.
- Several marketing pages still hotlink external third-party images. If any remain broken in production, replace them with locally hosted assets inside `public/`.
