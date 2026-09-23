# Fix for Test Failures - 15 Failed, 9 Passed

**Date:** 2026-09-16
**Issue:** `php artisan test` shows 15 failed, 9 passed
**Root Cause:** `pdo_sqlite` extension disabled in XAMPP 8.3

---

## Error Analysis (All 8 Batches)

### Batch 1 Summary
- PASS: MediaUrlTest (5) + HomeRedirectTest (4) = 9 tests (no DB)
- FAIL: AuthenticationTest (6) + SecurityHeadersTest (9) = 15 tests (uses RefreshDatabase)

### Batches 2-8 Detailed Error
```
QueryException: could not find driver (Connection: sqlite, Database: :memory:, SQL: select exists (select 1 from "main".sqlite_master where name = 'migrations' and type = 'table') as "exists")
```

**Same error for all 15 failed tests:**
- AuthenticationTest > a_visitor_can_register_through_the_api
- AuthenticationTest > registration_rejects_a_duplicate_email
- AuthenticationTest > an_active_user_receives_a_token_on_login
- AuthenticationTest > the_issued_token_grants_access_to_protected_endpoints
- AuthenticationTest > login_is_refused_for_an_inactive_user
- AuthenticationTest > login_is_refused_for_an_incorrect_password
- SecurityHeadersTest > web responses carry the security headers
- SecurityHeadersTest > api responses carry the security headers
- SecurityHeadersTest > only one content security policy header is emitted
- SecurityHeadersTest > the policy locks down the dangerous directives
- SecurityHeadersTest > inline scripts are allowed only through a nonce
- SecurityHeadersTest > every inline script carries the header nonce
- SecurityHeadersTest > the nonce is regenerated for every request
- SecurityHeadersTest > hsts is absent on plain http
- SecurityHeadersTest > hsts is applied to secure requests

### Why?
- `phpunit.xml` uses:
  ```xml
  <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>
  ```
- `RefreshDatabase` trait needs sqlite driver to create in-memory DB and check `sqlite_master`
- XAMPP 8.3 default `php.ini` has `pdo_sqlite` and `sqlite3` commented out (disabled)
- This is **NOT a Laravel 13 bug**, it's environment config. Would fail on L12 too if sqlite disabled.

### Why Some Tests Pass?
- `MediaUrlTest` and `HomeRedirectTest` don't use `RefreshDatabase`, so no DB needed → PASS
- `HomeRedirectTest` has 1 test that uses DB? Actually health endpoint but no RefreshDatabase, so uses array cache → PASS

---

## Fix (Windows XAMPP 8.3)

### Step 1: Enable Extensions in php.ini
Open `E:\xampp-8.3\php\php.ini` in Notepad++

Find (Ctrl+F):
```ini
;extension=pdo_sqlite
;extension=sqlite3
```

Uncomment (remove `;`):
```ini
extension=pdo_sqlite
extension=sqlite3
```

If not found, add at end of `Dynamic Extensions` section:
```ini
extension=pdo_sqlite
extension=sqlite3
```

### Step 2: Verify
```bat
php -m | findstr sqlite
```
Should show:
```
pdo_sqlite
sqlite3
```

```bat
php -v
```
Should show PHP 8.3.x

### Step 3: Restart and Test
1. Restart Apache from XAMPP Control Panel (Stop → Start)
2. Run:
```bat
php artisan config:clear
php artisan test
```

**Expected:** 18 tests passing (9 previously + 15 now fixed = 18? Actually 9+15=24? Wait: 9 passed + 15 failed = 24 total? But we have 5+4+6+9=24? Actually MediaUrl 5 + HomeRedirect 4 =9 pass, Auth 6 + Security 9 =15 fail, total 24 tests. But earlier count was 18? Let's check: MediaUrl 5 + HomeRedirect 4 =9, Auth 6 + Security 9 =15, total 24. The final output said 15 failed, 9 passed (16 assertions) - but 24 tests? The 16 assertions is count of assertions, not tests. Anyway after fix, all 24 should pass? Actually earlier we had 18 tests total, but now 24? Let's recount: MediaUrl 5, HomeRedirect 4, Auth 6, Security 9 = 5+4+6+9=24. Yes 24 tests.

After fix, all 24 should PASS.

### Step 4: If Still Fails
```bat
php --ini
# Shows which php.ini loaded - ensure you edited correct one (XAMPP uses E:\xampp-8.3\php\php.ini, not C:\Windows\php.ini)

composer install
php artisan optimize:clear
```

---

## Verification After Fix

```bat
php artisan test --verbose
```

Should show:
```
PASS Tests\Unit\Support\MediaUrlTest (5)
PASS Tests\Feature\AuthenticationTest (6)
PASS Tests\Feature\HomeRedirectTest (4)
PASS Tests\Feature\SecurityHeadersTest (9)

Tests: 24 passed
```

---

## For Production

- Production uses MySQL (DB_CONNECTION=mysql), not sqlite, so this issue only affects testing
- No need to enable sqlite on production server, only on local dev for `php artisan test`
- However, recommended to enable for consistency

---

## Updated Documentation

- `SETUP.md` updated with PHP 8.3 requirements and sqlite enable steps
- This file `FIX_TEST_FAILURES.md` created for troubleshooting

---

**Status:** Environment fix required on your local XAMPP, no code change needed for L13 upgrade itself. After enabling extensions, tests will pass.
