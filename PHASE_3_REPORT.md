# PHASE 3 Report: Laravel 13 Core Upgrade

**Date:** 2026-09-16
**Branch:** arena/01a0aa83-starterkitfree
**Status:** File changes done, static checks passed, runtime verification pending on local
**Previous Phases:** Phase 1 (PHP 8.3) ✅, Phase 2 (Permission v7) ✅

---

## Goal
Laravel framework ko 12 → 13 pe upgrade karna, saath me config sync aur security hardening.

---

## Changes Kiye

### 1. composer.json - Core Framework Upgrade
**Before (Phase 2):**
```json
"laravel/framework": "^12.0",
"laravel/tinker": "^2.10.1",
"phpunit/phpunit": "^11.5.50"
```

**After (Phase 3):**
```json
"laravel/framework": "^13.0",
"laravel/tinker": "^3.0",
"phpunit/phpunit": "^12.0"
```

**Full require:**
```json
"php": "^8.3",
"barryvdh/laravel-dompdf": "^3.1.2",
"intervention/image": "^3.11",
"laravel/framework": "^13.0",
"laravel/sanctum": "^4.3.3",
"laravel/tinker": "^3.0",
"spatie/laravel-permission": "^7.0"
```

**Reason:** Official upgrade guide [5](https://laravel.com/docs/13.x/upgrade) ke according high-impact changes:
- `laravel/framework ^13.0`
- `laravel/tinker ^3.0`
- `phpunit ^12.0`
- `pest ^4.0` (if used, not in this project)
- `laravel/boost ^2.0` (if used, not in this project)

**Platform:** Still `8.3.0` (from Phase 1)

### 2. config/cache.php - L13 Skeleton Sync

**Added from L13 skeleton (/tmp/laravel-skeleton/config/cache.php):**

**a) New store `storage`:**
```php
'storage' => [
    'driver' => 'storage',
    'disk' => env('CACHE_STORAGE_DISK'),
    'path' => env('CACHE_STORAGE_PATH', 'framework/cache/data'),
],
```
This is optional but added for completeness - L13 has new storage driver.

**b) Critical: `serializable_classes`:**
```php
'serializable_classes' => env('CACHE_SERIALIZABLE_CLASSES', false),
```
**Why?** L13 me security hardening: by default no PHP classes unserialized from cache to prevent gadget chain attacks if APP_KEY leaked [1](https://laravel.com/docs/13.x/upgrade). 

**Options:**
- `false` = Hardened (L13 default) - no objects allowed
- `true` = L12 behavior - all objects allowed (for seamless upgrade)
- `array` = Allow list: `[App\Support\StatisticCard::class]`

**Your project:** `DashboardService` me arrays cache karte ho, objects nahi, so `false` safe hai. Env var se control kar sakte ho.

### 3. config/session.php - L13 Skeleton Sync

**Added from L13 skeleton:**

```php
'serialization' => env('SESSION_SERIALIZATION', 'php'),
```

**Why?** L13 skeleton default `json` hai, but for seamless upgrade we set default to `php` (L12 behavior). 

**Options:**
- `json` = Hardened, no PHP objects in session, prevents gadget chain attacks, but **invalidates all active sessions** (users logout)
- `php` = L12 behavior, allows objects, seamless upgrade

**Recommendation:**
- Phase 3 me `php` rakho for zero-downtime
- Next minor release me `json` pe migrate karo with announcement

**L13 skeleton default is `json`:** We set env default to `php` for now, but you can change to `json` in .env when ready.

### 4. .env.example - Pinned Prefixes (CRITICAL)

**Added:**

```env
SESSION_COOKIE=laravel-business-starter-kit-session
SESSION_SECURE_COOKIE=true
SESSION_SERIALIZATION=php

CACHE_PREFIX=laravel-business-starter-kit-cache-
CACHE_SERIALIZABLE_CLASSES=false

REDIS_PREFIX=laravel-business-starter-kit-database-
```

**Why Critical?** L12 default prefixes used underscores (`my_app_cache_`), L13 uses hyphens (`my-app-cache-`) [5](https://laravel.com/docs/13.x/upgrade). If you don't pin explicitly in .env, deploy pe:
- Saara cache invalid ho jayega
- Saare users logout ho jayenge (session cookie name change)

**Your project already had hyphenated prefixes in config files:**
```php
// config/cache.php
'prefix' => env('CACHE_PREFIX', Str::slug(APP_NAME).'-cache-')
```
This already matches L13 new default, but pinning in .env ensures explicit control and prevents future framework default changes from affecting you.

### 5. app/Notifications - L13 Improvement (Optional but Recommended)

**Added `#[DeleteWhenMissingModels]` attribute:**

**Before:**
```php
final class WelcomeNotification extends Notification implements ShouldQueue
```

**After:**
```php
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

#[DeleteWhenMissingModels]
final class WelcomeNotification extends Notification implements ShouldQueue
```

**Same for `PasswordChangedNotification`.**

**Why?** L13 me queued notifications now respect `#[DeleteWhenMissingModels]` attribute [5](https://laravel.com/docs/13.x/upgrade). If user deleted before notification processes, job will be deleted instead of failing.

**Very Low Impact:** If you don't add, old behavior continues (job fails). Adding is safer.

---

## Static Checks Performed (Before Push)

### High Impact Checks
- [x] `composer.json` valid JSON, has `^13.0`, `^3.0`, `^12.0`
- [x] `VerifyCsrfToken` / `ValidateCsrfToken` references: **0 found** ✅
  - L13 renamed to `PreventRequestForgery`, old aliases still work but deprecated
  - Your project uses new `bootstrap/app.php` style, no direct references

### Medium Impact Checks
- [x] `serializable_classes` added to cache.php ✅
- [x] `serialization` added to session.php ✅
- [x] Cache usage: `DashboardService` caches arrays, not objects → safe for `false`
- [x] Session usage: No PHP objects stored in session → safe for `json` future

### Low Impact Checks
- [x] `QueueBusy` event: **0 found** ✅ (property renamed to `connectionName`)
- [x] `array_first` / `array_last` global helpers: **0 found** ✅ (Symfony polyfill conflict avoided)
- [x] `upsert` with empty uniqueBy: **0 found** ✅
- [x] `DELETE ... JOIN` with ORDER BY/LIMIT: **0 found** ✅
- [x] Model booting nested instantiation: **0 found** ✅ (HasAuditColumns only registers callback)
- [x] `Container::call` with nullable: **0 found** ✅
- [x] Custom cache store: **None** ✅ (would need `touch()` method)
- [x] Custom queue driver: **None** ✅
- [x] Custom dispatcher: **None** ✅
- [x] Domain routes: **None** ✅ (precedence change not affecting)
- [x] Manager extend binding: **Safe** (MenuBuilder singleton without $this)
- [x] Str factories: **None** ✅

### Config Checks
- [x] `config/cache.php` has `serializable_classes` and `prefix` with env
- [x] `config/session.php` has `serialization` and `cookie` with env
- [x] `.env.example` has pinned `CACHE_PREFIX`, `REDIS_PREFIX`, `SESSION_COOKIE`
- [x] L13 skeleton diff done via `git clone laravel/laravel 13.x`

---

## Local Machine Pe Kya Karna Hai

### Step 1: Composer Update (MAIN STEP)
```bash
cd starterKitFree
php -v # Must be 8.3+

# Backup
cp composer.lock composer.lock.l12.bak

# Full update to L13
composer update --with-all-dependencies -W

# If conflict, try:
composer update laravel/framework laravel/tinker phpunit/phpunit --with-all-dependencies -W

# Check
php artisan --version
# Should show Laravel 13.x
```

**Expected:** No errors, lock file updated to v13.x

### Step 2: Clear Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Config & Env
```bash
# Ensure .env has pinned values (from .env.example)
# If not, add:
CACHE_PREFIX=laravel-business-starter-kit-cache-
REDIS_PREFIX=laravel-business-starter-kit-database-
SESSION_COOKIE=laravel-business-starter-kit-session
SESSION_SERIALIZATION=php
CACHE_SERIALIZABLE_CLASSES=false

# Then:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Tests
```bash
php artisan test
# All should be green, but watch for:
# - PHPUnit 12 deprecations
# - serializable_classes false causing Incomplete_Class (if you cache objects)

vendor/bin/phpstan analyse
vendor/bin/pint --test
```

### Step 5: Manual Smoke Tests (Critical)
- [ ] `php artisan about` shows L13, PHP 8.3
- [ ] Registration: /register → user created, welcome mail logged
- [ ] Login: admin@example.com / Password!2345 → dashboard
- [ ] Dashboard stats: total_users etc. (Cache::remember test)
- [ ] Profile update, avatar upload (intervention/image test)
- [ ] Password change
- [ ] Roles: Manager sees more menu than Viewer (permission test)
- [ ] API: /api/v1/login → token, /api/v1/me with Bearer
- [ ] Session persistence: login → refresh → still logged in (cookie name test)
- [ ] Queue: Register → SendWelcomeNotification queued → `php artisan queue:work` processes
- [ ] Check logs: `storage/logs/laravel.log` no errors

### Step 6: Frontend
```bash
npm install
npm run build
# Should be clean, Vite 7 + Tailwind 4 compatible
```

### Step 7: Commit
```bash
git add composer.json composer.lock config/cache.php config/session.php .env.example app/Notifications/
git commit -m "phase3: upgrade to Laravel 13.0 core

- Bump framework ^12.0 -> ^13.0, tinker ^2.10.1 -> ^3.0, phpunit ^11.5.50 -> ^12.0
- Sync config/cache.php with L13 skeleton: add serializable_classes=false, storage driver
- Sync config/session.php with L13 skeleton: add serialization=php (for seamless)
- Pin CACHE_PREFIX, REDIS_PREFIX, SESSION_COOKIE in .env.example
- Add DeleteWhenMissingModels to notifications for L13
- All static checks passed"
```

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation | Status |
|------|------------|--------|------------|--------|
| Cache invalid / users logout | High if not pinned | Medium | Pinned in .env.example, set env default | ✅ Mitigated |
| serializable_classes=false breaks object cache | Low (you cache arrays) | Medium | Set to false, but env allows true for rollback | ✅ Mitigated |
| Session json invalidates sessions | Low (we keep php) | Medium | Default php for seamless, json later | ✅ Mitigated |
| PHPUnit 11->12 breaking | Low | Low | Run tests, fix deprecations | ⏳ To verify locally |
| Package incompatibility | Low (Phase 2 fixed blockers) | Medium | All blockers resolved in Phase 2 | ✅ Mitigated |
| Queue job serialization | Very Low | Medium | Drain queues before deploy | ⏳ To verify in staging |

---

## Files Changed

1. **composer.json** - Framework, tinker, phpunit bumped to L13
2. **config/cache.php** - Added `serializable_classes` and `storage` driver
3. **config/session.php** - Added `serialization`
4. **.env.example** - Added pinned prefixes and new options
5. **app/Notifications/WelcomeNotification.php** - Added DeleteWhenMissingModels
6. **app/Notifications/PasswordChangedNotification.php** - Added DeleteWhenMissingModels
7. **PHASE_3_REPORT.md** - This file

---

## Next Steps - Phase 4, 5, 6, 7

**Phase 4: Code Adaptation** (Already partially done in Phase 3)
- Optional: Adopt PHP Attributes for middleware, Cache::touch(), etc.
- Currently no mandatory code changes needed

**Phase 5: Testing & QA** (4-6h)
- Full automated tests
- Manual smoke tests (list above)
- Performance check

**Phase 6: Staging Deploy** (2h)
- Deploy to staging with PHP 8.3
- Test with production-like data
- Monitor 4h

**Phase 7: Production Rollout** (1h + 48h monitoring)
- Atomic deploy, no downtime
- Rollback plan ready (backup branch + lock file)

---

## Verification Checklist (For You)

- [ ] `composer.json` shows `laravel/framework: ^13.0`
- [ ] `composer update -W` successful, no errors
- [ ] `php artisan --version` shows 13.x
- [ ] `php artisan optimize:clear` successful
- [ ] `config/cache.php` has `serializable_classes`
- [ ] `config/session.php` has `serialization`
- [ ] `.env.example` has pinned prefixes
- [ ] `php artisan test` green
- [ ] Manual smoke tests passed (login, dashboard, permissions, media upload, API)
- [ ] `npm run build` successful
- [ ] `composer.lock` committed

---

**Phase 3 Status:** ✅ File changes done, static checks passed, ⏳ Runtime verification pending on your local
**Overall Progress:** 3/7 phases complete (43%)
**Next:** Phase 4-5 Testing, then Staging & Production

**Note:** This branch now has Laravel 13 core files. On your local, `composer update` will pull actual L13 code and you can test. In this sandbox, PHP not available, so runtime verification pending.
