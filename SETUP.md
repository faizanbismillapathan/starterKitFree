# Local Setup — Laravel Business Starter Kit (Free Edition)

## Requirements

| Component | Minimum | Notes |
|---|---|---|
| PHP | **8.2** | XAMPP 8.2.12 works. `composer.lock` is pinned to 8.2 |
| MySQL | 8.0+ | utf8mb4 / InnoDB |
| Composer | 2.x | |
| Node.js | LTS (18/20/22) | For building the frontend assets |

Required PHP extensions: `mbstring`, `openssl`, `pdo_mysql`, `curl`, `fileinfo`, `gd`, `zip`, `intl`, `tokenizer`, `xml`.

---

## Windows / XAMPP quick start

### 1. Disable the broken imagick extension (optional but removes the warning)

Open `E:\xampp-8.2\php\php.ini` and comment the line out:

```ini
;extension=imagick
```

This project does **not** use imagick — image processing runs on the GD driver.

### 2. Install PHP dependencies

```bat
composer install
```

The lock file is resolved for PHP 8.2, so this installs cleanly with no
`composer update` required.

### 3. Create the environment file

```bat
copy .env.example .env
php artisan key:generate
```

### 4. Create the database

In phpMyAdmin (or the MySQL CLI) create an empty schema:

```sql
CREATE DATABASE starter_kit
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Then confirm the credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=starter_kit
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations and seeders

```bat
php artisan migrate --seed
```

### 6. Link the storage disk (required for avatars)

```bat
php artisan storage:link
```

### 7. Build the frontend

```bat
npm install
npm run build
```

For live reloading during development use `npm run dev` in a second terminal.

### 8. Serve the application

```bat
php artisan serve
```

Open <http://127.0.0.1:8000>.

---

## Seeded accounts

Available in local/development environments only.

| Role | Email | Password |
|---|---|---|
| Super Administrator | `admin@example.com` | `Password!2345` |
| Manager | `manager@example.com` | `Password!2345` |
| Viewer | `viewer@example.com` | `Password!2345` |

Change `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` before seeding a real
deployment. The `UserSeeder` creates only the administrator when
`APP_ENV=production`.

---

## Email verification in local development

`MAIL_MAILER=log` is the default, so verification and welcome emails are
written to `storage/logs/laravel.log` rather than being sent. Open that file
and follow the signed URL to verify an account.

To skip verification entirely while developing:

```env
AUTH_EMAIL_VERIFICATION_REQUIRED=false
```

---

## Useful commands

```bat
php artisan test              :: run the full test suite
vendor\bin\pint               :: format code to PSR-12
vendor\bin\phpstan analyse    :: static analysis
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

## Troubleshooting

**`Your lock file does not contain a compatible set of packages`**
You are on an older PHP than 8.2, or the lock file was generated elsewhere.
Check with `php -v`, then run `composer install` again.

**`Please provide a valid cache path` / permission errors**
Ensure `storage/` and `bootstrap/cache/` are writable.

**Avatar images return 404**
Run `php artisan storage:link`.

**`SQLSTATE[HY000] [1049] Unknown database`**
The schema in `DB_DATABASE` does not exist yet — create it (step 4).
