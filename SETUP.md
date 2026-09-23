# Local Setup — Laravel Business Starter Kit (Free Edition)

## Requirements

| Component | Minimum | Notes |
|---|---|---|
| PHP | **8.3** | XAMPP 8.3.12+ works. `composer.lock` is pinned to 8.3 for Laravel 13 |
| MySQL | 8.0+ | utf8mb4 / InnoDB |
| Composer | 2.x | |
| Node.js | LTS (18/20/22) | For building the frontend assets |

Required PHP extensions: `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `curl`, `fileinfo`, `gd`, `zip`, `intl`, `tokenizer`, `xml`, `bcmath`, `ctype`.

> **Important for Testing:** `phpunit.xml` uses `sqlite :memory:` for fast tests. You MUST enable `pdo_sqlite` and `sqlite3` in `php.ini` or you will get `could not find driver (Connection: sqlite)` error for 15 tests (6 Authentication + 9 SecurityHeaders).

---

## Windows / XAMPP quick start

### 1. Fix PHP extensions for XAMPP 8.3 (CRITICAL for Laravel 13 + Tests)

Open `E:\xampp-8.3\php\php.ini`:

**a) Disable imagick (optional - removes warning):**
```ini
;extension=imagick
```
Project uses GD driver, not imagick.

**b) Enable sqlite for testing (REQUIRED - 15 tests fail without it):**
Find and uncomment (remove `;`):
```ini
extension=pdo_sqlite
extension=sqlite3
```
If not found, add them at the end of extensions section.

**c) Verify and restart:**
```bat
php -m | findstr sqlite
# Should show: pdo_sqlite, sqlite3

php -v
# Should show: PHP 8.3.x
```
Then restart Apache from XAMPP Control Panel.

### 2. Install PHP dependencies

```bat
composer install
```

For Laravel 13 upgrade:
```bat
composer update --with-all-dependencies -W
```

The lock file is now pinned to PHP 8.3 for Laravel 13.

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
php artisan test              :: run the full test suite (requires pdo_sqlite enabled)
vendor\bin\pint               :: format code to PSR-12
vendor\bin\phpstan analyse    :: static analysis
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

## Troubleshooting

**`Your lock file does not contain a compatible set of packages`**
You are on an older PHP than 8.3, or the lock file was generated elsewhere.
Check with `php -v`, then run `composer install` again.

**`could not find driver (Connection: sqlite, Database: :memory:)`**
You have 15 tests failing (Authentication + SecurityHeaders). Fix:
1. Open `E:\xampp-8.3\php\php.ini`
2. Uncomment `extension=pdo_sqlite` and `extension=sqlite3`
3. Restart Apache
4. Verify `php -m | findstr sqlite` shows both
5. Run `php artisan test` again - should be 18 tests passing

This is NOT a Laravel 13 bug, it's XAMPP default config - sqlite disabled by default.

**`Please provide a valid cache path` / permission errors**
Ensure `storage/` and `bootstrap/cache/` are writable.

**Avatar images return 404**
Run `php artisan storage:link`.

**`SQLSTATE[HY000] [1049] Unknown database`**
The schema in `DB_DATABASE` does not exist yet — create it (step 4).

**Cache invalid / users logout after L13 deploy**
Ensure `.env` has pinned values from `.env.example`:
```
CACHE_PREFIX=laravel-business-starter-kit-cache-
REDIS_PREFIX=laravel-business-starter-kit-database-
SESSION_COOKIE=laravel-business-starter-kit-session
```
