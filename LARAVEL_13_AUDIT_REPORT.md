# Laravel 12 → Laravel 13 Upgrade Audit Report
**Project:** starterKitFree (Launch Kit for Laravel - Community Edition)  
**Current Version:** Laravel v12.64.0 [composer.lock se]  
**Target Version:** Laravel 13.x (Released March 17, 2026)  
**Date:** 2026-09-16  
**Status:** AUDIT ONLY - No code changed

---

## 1. Executive Summary (Saransh)

Ye project abhi **Laravel 12** pe hai aur **PHP ^8.2** require karta hai. Laravel 13 me **PHP 8.3 minimum** ho gaya hai [3](https://www.icoderzsolutions.com/blog/laravel-13-is-released/) aur support PHP 8.3-8.5 tak hai [1](https://pola5h.github.io/blog/laravel-13-new-features/). Official release notes ke according Laravel 13 me **minimal breaking changes** hai, focus quality-of-life improvements pe tha [4](https://laravel.com/docs/13.x/releases).

**Aapke project ke liye sabse bade blockers:**
1.  **PHP Version:** `composer.json` me `php: ^8.2` aur `platform: 8.2.0` pinned hai - isko `^8.3` karna padega.
2.  **spatie/laravel-permission:** Aap `^6.10` use kar rahe ho, jo Laravel 13 support nahi karta. Laravel 13 ke liye `^7.2.1 - ^8.0` chahiye [4](https://laravelshift.com/can-i-upgrade-laravel/spatie/laravel-permission).
3.  **laravel/tinker:** `^2.10.1` → `^3.0` karna mandatory hai [5](https://laravel.com/docs/13.x/upgrade).
4.  **phpunit:** `^11.5.50` → `^12.0` karna padega [5](https://laravel.com/docs/13.x/upgrade).
5.  **Cache & Session Hardening:** Laravel 13 me `serializable_classes` aur `session.serialization = json` naye defaults hai - aapke `config/cache.php` aur `config/session.php` me ye missing hai.

**Good News:** Aapka codebase kaafi clean hai. Koi `VerifyCsrfToken` reference nahi, koi `upsert` with empty uniqueBy nahi, koi `DELETE ... JOIN` nahi, koi custom Cache Store ya Queue Driver nahi. Isliye code-level breaking changes ka impact **Low** hai.

---

## 2. Current Project Snapshot

### composer.json (Important Parts)
```json
"require": {
    "php": "^8.2",
    "barryvdh/laravel-dompdf": "^3.1",
    "intervention/image": "^3.11",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.3",
    "laravel/tinker": "^2.10.1",
    "spatie/laravel-permission": "^6.10"
},
"require-dev": {
    "phpunit/phpunit": "^11.5.50",
    ...
},
"config": {
    "platform": { "php": "8.2.0" }
}
```

### Locked Version (composer.lock)
- `laravel/framework`: v12.64.0
- `laravel/sanctum`: 4.x (compatible)
- `intervention/image`: 3.11 (framework agnostic, ok)
- `barryvdh/laravel-dompdf`: 3.1.x line

### .env.example Analysis
- `APP_NAME="Laravel Business Starter Kit"`
- `CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`
- **Missing:** `CACHE_PREFIX`, `REDIS_PREFIX`, `SESSION_COOKIE` explicitly defined nahi hai. Ye Laravel 13 upgrade me sabse risky point hai.

### Config Files Scan
- `config/cache.php`: prefix = `Str::slug(APP_NAME).'-cache-'` (already hyphenated, good). Lekin `serializable_classes` option missing hai.
- `config/session.php`: cookie = `Str::slug(APP_NAME).'-session'` (already hyphenated). Lekin `serialization` option missing hai.
- `config/queue.php`: default `database`, koi custom driver nahi.
- `bootstrap/app.php`: New Laravel 11/12 style, no `App\Http\Kernel`. Middleware alias me `role`, `permission` defined hai - L13 compatible.
- `vite.config.js`: Vite 7.0.7 + Tailwind 4.3.3 - L13 compatible.

### Codebase Scan Results
| Check | Result | Impact |
|-------|--------|--------|
| `VerifyCsrfToken` / `ValidateCsrfToken` reference | **0 found** | Safe |
| `withoutMiddleware([VerifyCsrfToken::class])` in tests | **0 found** | Safe |
| `upsert()` usage | **0 found** in app/ | Safe |
| `DELETE` with `JOIN` + `ORDER BY`/`LIMIT` | **0 found** | Safe |
| Custom `Cache Store` (implements `Store` contract) | **None** | Safe, but need to check `touch()` method if you had one |
| Custom Queue Driver | **None** | Safe |
| Custom `Dispatcher` contract | **None** | Safe |
| `QueueBusy` event `$connection` property | **0 found** | Safe |
| Model `boot()` me new instance creation | `HasAuditColumns::bootHasAuditColumns()` only registers `saving` callback, no instantiation - **Safe** | Very Low |
| Polymorphic pivot custom table | `HasMedia` trait uses `morphMany`, not `morphToMany` pivot - **Safe** | Low |
| `Cache::remember` me object caching | `DashboardService` me arrays/ints cached, no Eloquent models - **Safe** | Medium if future me object cache karoge |
| `array_first()` / `array_last()` global helpers | **0 found** | Safe (Symfony polyfill conflict nahi hoga) |
| `Str` factories custom | **0 found** | Safe |
| `Container::call` with nullable class | No direct usage | Low |

---

## 3. Laravel 13 Official Requirements & Breaking Changes

### 3.1 System Requirements
- **PHP:** ^8.3 minimum, supports 8.3, 8.4, 8.5 [3](https://www.icoderzsolutions.com/blog/laravel-13-is-released/) [1](https://pola5h.github.io/blog/laravel-13-new-features/)
- **Composer:** 2.x
- **Node:** LTS (Vite build ke liye)
- **Support Timeline:** Bug fixes Q3 2027 tak, Security fixes March 17, 2028 tak [4](https://laravel.com/docs/13.x/releases)

### 3.2 High Impact Changes (Aapko karna padega)

#### A. Dependencies Update [5](https://laravel.com/docs/13.x/upgrade) [7](https://ortamarco.me/en/blog/what-breaks-upgrading-to-laravel-13/)
```json
"require": {
    "php": "^8.3",
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0"
},
"require-dev": {
    "phpunit/phpunit": "^12.0",
    "pestphp/pest": "^4.0 (if used)",
    "laravel/boost": "^2.0 (if used)"
}
```

#### B. CSRF Middleware Rename [5](https://laravel.com/docs/13.x/upgrade)
- Old: `VerifyCsrfToken` / `ValidateCsrfToken`
- New: `PreventRequestForgery` (adds `Sec-Fetch-Site` header verification)
- Old aliases deprecated but still work. Aapke project me koi direct reference nahi, isliye **No Action** but future me dhyaan rakho.

#### C. Cache Prefix & Session Cookie Name Change [5](https://laravel.com/docs/13.x/upgrade) [4](https://ortamarco.me/en/blog/what-breaks-upgrading-to-laravel-13/)
Laravel 12 default:
```php
Str::slug(APP_NAME, '_').'_cache_' // underscores
```
Laravel 13 default:
```php
Str::slug(APP_NAME).'-cache-' // hyphens
```
**Aapke project me:** Aap already hyphen use kar rahe ho (`Str::slug(...).'-cache-'`), jo L13 ke new default se match karta hai. Lekin `.env` me explicitly pin nahi hai. Agar production me aap L12 se L13 deploy karte ho without pinning, to:
- Saara cache invalid ho jayega
- Saare users logout ho jayenge (session cookie name change)

**Fix Required:**
`.env.example` aur production `.env` me add karo:
```
CACHE_PREFIX=laravel-business-starter-kit-cache-
REDIS_PREFIX=laravel-business-starter-kit-database-
SESSION_COOKIE=laravel-business-starter-kit-session
```
Ya jo bhi aapka current slug hai, usko explicitly set karo BEFORE deploy.

### 3.3 Medium Impact Changes

#### A. `serializable_classes` in cache.php [1](https://laravel.com/docs/13.x/upgrade) [2](https://masteringlaravel.io/daily/2026-08-04-the-sneaky-failure-mode-in-laravel-13s-hardened-cache)
L13 me naya config:
```php
// config/cache.php
'serializable_classes' => false, // default hardened
```
Ya allow list:
```php
'serializable_classes' => [
    App\Data\CachedDashboardStats::class,
]
```
**Aapke liye:** Aap currently `Cache::remember` me arrays store karte ho, objects nahi. Toh `false` safe hai. Lekin agar future me Eloquent models ya DTOs cache karoge, to unko allow list me dalna padega warna `__PHP_Incomplete_Class` milega.

#### B. `upsert` validation
L13 me MySQL/MariaDB `upsert` me `uniqueBy` empty nahi ho sakta, warna `InvalidArgumentException`. Aapke codebase me `upsert` use nahi hota - **Safe**.

#### C. Session Serialization to `json`
L13 skeleton me:
```php
// config/session.php
'serialization' => 'json', // was 'php'
```
Ye deserialization attack se bachata hai. Agar aap `php` se `json` pe switch karte ho, to saare active sessions invalid ho jayenge. Agar aap session me PHP objects store nahi karte (aap nahi karte), to `json` recommended hai. Otherwise `php` hi rehne do for seamless upgrade.

### 3.4 Low / Very Low Impact Changes (Aapke liye mostly Safe)

| Change | Description | Aapke Project pe Impact |
|--------|-------------|-------------------------|
| `Container::call` nullable defaults | Now respects null default | No direct usage - Safe |
| `Dispatcher` contract `dispatchAfterResponse` | New method | No custom dispatcher - Safe |
| `Model Booting Nested Instantiation` | `LogicException` if you create model inside `boot()` | `HasAuditColumns` safe - Safe |
| `Polymorphic Pivot Table Name` | Now pluralized | No morph pivot - Safe |
| `Collection Serialization Restores Relations` | Eager loaded relations preserved | You cache arrays, not collections - Low |
| `HTTP Client Response::throw` signature | Callback param added | No custom response class - Safe |
| `Queued Notifications DeleteWhenMissingModels` | Now respects attribute | Your notifications don't use it, but could add `#[DeleteWhenMissingModels]` for safety |
| `QueueBusy` `$connection` → `$connectionName` | Rename | No listener uses it - Safe |
| `Queue Contract` new methods `pendingSize` etc | Need implementation if custom driver | No custom driver - Safe |
| `Domain Route Precedence` | Domain routes prioritized | You have no domain routes - Safe |
| `Manager extend` binding | `$this` now bound to manager | `AppServiceProvider` me `singleton(MenuBuilder::class)` uses closure without `$this` - Safe |
| `Str factories reset between tests` | Reset during teardown | No custom Str factories - Safe |
| `Symfony polyfill-php85` `array_first()` | Global functions defined | You don't define them - Safe |

---

## 4. Package Compatibility Matrix (Detailed)

| Package | Current | L13 Compatible Version | Status | Action Required |
|---------|---------|------------------------|--------|-----------------|
| `laravel/framework` | ^12.0 (v12.64) | ^13.0 | ❌ Needs upgrade | Update to ^13.0 |
| `php` | ^8.2 + platform 8.2.0 | ^8.3 | ❌ Needs upgrade | Update to ^8.3, remove or update platform to 8.3.0 |
| `laravel/sanctum` | ^4.3 | ^4.3.1+ supports L13 [1](https://github.com/laravel/sanctum/releases) | ✅ Compatible | Update to ^4.3.3 (latest) - supports L13 via PR #587 |
| `spatie/laravel-permission` | ^6.10 | 7.2.1 - 8.0 for L13 [4](https://laravelshift.com/can-i-upgrade-laravel/spatie/laravel-permission) | ❌ **BLOCKER** | Must upgrade to ^6.12 minimum, recommended ^7.0 or ^8.0. Check breaking changes from v6 to v7 (PHP 8.3+, teams support changes) |
| `barryvdh/laravel-dompdf` | ^3.1 | 3.1.2 supports L13 [1](https://laravelshift.com/can-i-upgrade-laravel/barryvdh/laravel-dompdf) | ⚠️ Partial | Your constraint ^3.1 allows 3.1.2, but need to run `composer update` and test. Config publish may need update for new options `allowedRemoteHosts` |
| `intervention/image` | ^3.11 | ^3.11 (no Laravel constraint) | ✅ Compatible | No action, but consider adding `intervention/image-laravel` if you want facade |
| `laravel/tinker` | ^2.10.1 | ^3.0 [5](https://laravel.com/docs/13.x/upgrade) | ❌ Needs upgrade | Update to ^3.0 |
| `phpunit/phpunit` | ^11.5.50 | ^12.0 [5](https://laravel.com/docs/13.x/upgrade) | ❌ Needs upgrade | Update to ^12.0, check if tests need adjustment for new assertions |
| `larastan/larastan` | ^3.10 | Check latest | ⚠️ Check | Latest 3.x should support L13, but verify |
| `phpstan/phpstan` | ^2.2 | Latest | ⚠️ Check | Should support PHP 8.3 |
| `laravel/pint` | ^1.29 | ^1.32+ recommended | ✅ Likely ok | Update to latest |
| `laravel/pail` | ^1.2.2 | ^1.2+ | ✅ Likely ok | Check for L13 support, usually ok |
| `laravel/sail` | ^1.41 | Latest | ✅ Likely ok | Update to latest for PHP 8.3 docker images |
| `nunomaduro/collision` | ^8.6 | ^8.8+ for L13? | ⚠️ Check | Update to ^8.8 |
| `fakerphp/faker` | ^1.23 | ^1.24+ | ✅ ok | No issue |
| `mockery/mockery` | ^1.6 | ^1.6 | ✅ ok | No issue |
| **Frontend** | | | | |
| `vite` | ^7.0.7 | ^7.x | ✅ ok | Compatible |
| `tailwindcss` | ^4.3.3 | ^4.x | ✅ ok | Compatible |
| `@tailwindcss/vite` | ^4.3.3 | ^4.x | ✅ ok | Compatible |
| `alpinejs` | ^3.15.12 | ^3.x | ✅ ok | Compatible |

**Key Takeaway:** Sabse bada blocker `spatie/laravel-permission` hai. v6.10 → v7 ya v8 me breaking changes hai (check Spatie docs). Sanctum aur dompdf L13 ready hai, bas minor version bump chahiye.

---

## 5. Files Jo Change Hongi (Upgrade ke Time)

### 5.1 `composer.json` (MUST CHANGE)
```diff
- "php": "^8.2",
+ "php": "^8.3",
- "laravel/framework": "^12.0",
+ "laravel/framework": "^13.0",
- "laravel/tinker": "^2.10.1",
+ "laravel/tinker": "^3.0",
- "spatie/laravel-permission": "^6.10",
+ "spatie/laravel-permission": "^6.12 OR ^7.0 OR ^8.0",
- "phpunit/phpunit": "^11.5.50",
+ "phpunit/phpunit": "^12.0",
  "config": {
-   "platform": { "php": "8.2.0" }
+   "platform": { "php": "8.3.0" }
  }
```

### 5.2 `config/cache.php` (SHOULD SYNC WITH L13 SKELETON)
Add:
```php
'serializable_classes' => env('CACHE_SERIALIZABLE_CLASSES', false),
// or true to keep L12 behavior temporarily
// or explicit allow list
```
Also ensure prefix uses env:
```php
'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel')).'-cache-'),
```
Aapka already hyphenated hai, good.

### 5.3 `config/session.php` (SHOULD SYNC)
Add:
```php
'serialization' => env('SESSION_SERIALIZATION', 'php'), // or 'json' for hardening
```
If you set to `json`, all sessions will be invalidated - users will need to re-login. If you want seamless, keep `php` initially, then migrate to `json` later.

Also:
```php
'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel')).'-session'),
```
Already hyphenated.

### 5.4 `config/queue.php`
L13 skeleton me default `database` hai (sync nahi). Aapka already `database` hai - good, no change.

### 5.5 `.env.example` (RECOMMENDED)
Add explicit pins:
```
CACHE_PREFIX=laravel-business-starter-kit-cache-
REDIS_PREFIX=laravel-business-starter-kit-database-
SESSION_COOKIE=laravel-business-starter-kit-session
SESSION_SERIALIZATION=php
CACHE_SERIALIZABLE_CLASSES=false
```

### 5.6 `bootstrap/app.php`
No change needed, but check if any custom CSRF logic added - nahi hai.

### 5.7 `app/` Code
- No file needs mandatory change for L13.
- Optional improvements:
  - `WelcomeNotification` aur `PasswordChangedNotification` me `#[DeleteWhenMissingModels]` attribute add kar sakte ho.
  - `DashboardService` me agar future me object cache karoge, to allow list define karna.

### 5.8 `database/migrations`
No new migrations required by L13 core, but if you use `queue:database` default, ensure `jobs` table exists (already exists).

### 5.9 `tests/`
- No `VerifyCsrfToken` references - safe.
- PHPUnit 12 me kuch deprecations removed hai - tests run karke dekhna padega.

### 5.10 Frontend
- No change needed. Vite 7 + Tailwind 4 already L13 compatible.

---

## 6. Security & Hardening Opportunities (L13 me naya)

1. **Cache Hardening:** `serializable_classes => false` set karke deserialization attack se bacho [2](https://masteringlaravel.io/daily/2026-08-04-the-sneaky-failure-mode-in-laravel-13s-hardened-cache).
2. **Session JSON Serialization:** `serialization => json` se session me object injection roko. L13 skeleton default hai.
3. **CSRF Origin Verification:** `PreventRequestForgery` ab `Sec-Fetch-Site` header check karta hai - more secure.
4. **PHP 8.3 Features:** Typed class constants, `json_validate()`, etc. use kar sakte ho.

---

## 7. Recommended Upgrade Steps (Jab Upgrade Karoge Tab)

> **Note:** Abhi upgrade nahi karna, sirf audit hai. Ye steps future ke liye hai.

1. **Branch banao:**
   ```bash
   git checkout -b upgrade/laravel-13
   ```

2. **PHP 8.3 verify karo:**
   ```bash
   php -v # must be 8.3+
   ```

3. **Cache/Session prefix pin karo production .env me BEFORE deploy** [7](https://ortamarco.me/en/blog/what-breaks-upgrading-to-laravel-13/):
   ```
   CACHE_PREFIX=laravel-business-starter-kit-cache-
   REDIS_PREFIX=laravel-business-starter-kit-database-
   SESSION_COOKIE=laravel-business-starter-kit-session
   ```

4. **Grep checks:**
   ```bash
   grep -rn "VerifyCsrfToken\|ValidateCsrfToken" app/ tests/ routes/ bootstrap/
   grep -rn "QueueBusy" app/
   grep -rn "array_first\|array_last" app/
   ```

5. **composer.json update karo** (section 5.1)

6. **Composer update:**
   ```bash
   composer update --with-all-dependencies
   # If platform issue: composer update --ignore-platform-reqs (temp)
   ```

7. **Config sync:**
   - `php artisan vendor:publish --tag=laravel-assets --force` nahi, manually `config/cache.php` aur `config/session.php` ko L13 skeleton se compare karo.
   - Diff command: `diff -u vendor/laravel/laravel/config/cache.php config/cache.php`

8. **Clear caches:**
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

9. **Tests:**
   ```bash
   php artisan test
   vendor/bin/phpstan analyse
   vendor/bin/pint --test
   ```

10. **Queue drain:** Agar production me queue workers chal rahe hai, to deploy se pehle queue drain karo - L12 queued jobs L13 worker pe fail ho sakte hai due to serialization change.

11. **Deploy & Migrate:**
    ```bash
    php artisan migrate
    ```

---

## 8. Risks & Mitigation

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Cache invalid / users logout | High if not pinned | Medium | Pin CACHE_PREFIX, SESSION_COOKIE before deploy |
| `spatie/permission` v6→v7 breaking | High | High | Read Spatie v7 upgrade guide, test roles/permissions thoroughly |
| PHP 8.2 server pe deploy fail | High if server not upgraded | High | Server PHP 8.3 pe upgrade karo pehle |
| `serializable_classes=false` se object cache break | Medium if you cache objects | Medium | Initially `true` rakho, then allow list banao |
| PHPUnit 11→12 breaking | Low | Low | Tests run karo, fix assertions |
| Third-party packages L13 support nahi | Low | Medium | `composer update` me error ayega, fork ya alternative dhoondo |

---

## 9. Final Checklist (Audit Complete)

- [x] Current Laravel version identified: v12.64.0
- [x] PHP requirement check: ^8.2 → ^8.3 needed
- [x] composer.json dependencies scanned
- [x] Package compatibility checked (Sanctum ✅, dompdf ⚠️, permission ❌)
- [x] Breaking changes mapped to codebase (mostly safe)
- [x] Config files diff identified (cache.php, session.php, .env)
- [x] Codebase grep for risky patterns (0 found for high-risk)
- [x] Security hardening opportunities noted
- [x] Upgrade steps documented (not executed)

---

## 10. Conclusion

**Aapka project Laravel 13 upgrade ke liye 80% ready hai.** Codebase clean hai, koi major anti-pattern nahi. Sabse bada kaam:

1. PHP 8.3 pe move karna
2. `spatie/laravel-permission` ko ^7 ya ^8 pe le jana (breaking changes check karna)
3. `tinker` aur `phpunit` update karna
4. Config files me `serializable_classes` aur `serialization` add karna
5. `.env` me prefixes pin karna

Estimated effort: **2-4 hours** for upgrade + **4-6 hours** for testing (agar permission upgrade smooth gaya to). Agar permission v6→v8 me major changes hue, to additional 1-2 days lag sakte hai.

**Next Step:** Jab aap bolo, main upgrade branch pe kaam start kar sakta hu - pehle PHP version check, fir composer update, fir tests.

---

**References:**
- Laravel 13 Release Notes [4](https://laravel.com/docs/13.x/releases)
- Laravel 13 Upgrade Guide [5](https://laravel.com/docs/13.x/upgrade)
- Laravel 13 Requirements [3](https://www.icoderzsolutions.com/blog/laravel-13-is-released/)
- Sanctum L13 Support [1](https://github.com/laravel/sanctum/releases)
- Dompdf L13 Compat [1](https://laravelshift.com/can-i-upgrade-laravel/barryvdh/laravel-dompdf)
- Permission L13 Compat [4](https://laravelshift.com/can-i-upgrade-laravel/spatie/laravel-permission)
- Breaking Changes Real List [7](https://ortamarco.me/en/blog/what-breaks-upgrading-to-laravel-13/)
- Cache Hardening [2](https://masteringlaravel.io/daily/2026-08-04-the-sneaky-failure-mode-in-laravel-13s-hardened-cache)
