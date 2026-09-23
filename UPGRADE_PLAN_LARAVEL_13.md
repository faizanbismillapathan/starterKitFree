# Laravel 12 → 13 Upgrade Plan - Phased Execution Strategy
**Project:** starterKitFree (Launch Kit)  
**Current:** Laravel 12.64 + PHP 8.2  
**Target:** Laravel 13.x + PHP 8.3  
**Total Estimated Time:** 2-3 Days (including QA)  
**Risk Level:** Medium (main blocker: spatie/laravel-permission)

---

## PHASE 0: Preparation & Audit ✅ DONE

**Status:** Complete - Report `LARAVEL_13_AUDIT_REPORT.md` me hai
**Kya kiya:**
- [x] composer.json, composer.lock scan
- [x] config files audit (cache, session, queue)
- [x] Codebase grep (VerifyCsrfToken, upsert, DELETE JOIN, etc.)
- [x] Package compatibility matrix

**Output:** Audit report ready

---

## PHASE 1: Environment Upgrade (Day 1 - Morning) - 2 Hours
**Goal:** PHP 8.3 pe sab environments le aana, bina Laravel upgrade kiye

### 1.1 Local Environment
```bash
# PHP version check
php -v  # must be 8.3.0+
# If using Herd/Valet/Laragon/XAMPP:
# - XAMPP: Download xampp 8.3.12, backup htdocs & php.ini
# - Herd: Select PHP 8.3 from menu
# - Docker: Update Dockerfile FROM php:8.3-fpm

# Composer check
composer --version # 2.x hona chahiye
composer self-update

# Node check
node -v # 18/20/22 LTS
npm -v
```

### 1.2 composer.json Platform Update (Is phase me Laravel 12 pe hi raho, sirf PHP bump)
```json
// composer.json me pehle ye karo, L12 pe hi test karo
"require": {
    "php": "^8.3"
},
"config": {
    "platform": { "php": "8.3.0" }
}
```
```bash
composer update --with-all-dependencies
php artisan test
# Agar sab green hai, to commit:
git add composer.json composer.lock
git commit -m "chore: bump PHP to 8.3 on Laravel 12"
```

**Why alag phase?** Best practice kehta hai PHP upgrade aur Framework upgrade alag deploy karo [2](https://muneebdev.com/laravel-13-when-to-upgrade-and-whats-actually-worth-it/) - "If 8.2 or below → upgrade PHP first, run the app on 8.3 for 1-2 weeks, then upgrade Laravel. Don't combine"

### 1.3 CI/CD & Server
- [ ] GitHub Actions / GitLab CI me `php-version: 8.3` set karo
- [ ] Staging server PHP 8.3 pe upgrade karo (Forge/Ploi me toggle)
- [ ] Production server PHP 8.3 readiness check (but abhi deploy mat karo)
- [ ] Extensions check: `mbstring, openssl, pdo_mysql, curl, fileinfo, gd, zip, intl, tokenizer, xml, bcmath, ctype, dom, json, pcre`

**Deliverable:** App Laravel 12 pe hi PHP 8.3 pe stable chal raha hai
**Rollback:** Simple - PHP 8.2 pe wapas

---

## PHASE 2: Dependency Blocker Resolution (Day 1 - Afternoon) - 3 Hours
**Goal:** `composer why-not` ko green karna

### 2.1 Blocker Check Command
```bash
composer why-not laravel/framework ^13.0
composer why-not spatie/laravel-permission ^7.0
composer why-not laravel/sanctum ^4.3.3
composer why-not barryvdh/laravel-dompdf ^3.1.2
```

### 2.2 Spatie Permission Upgrade - SABSE IMPORTANT
**Current:** ^6.10 → **Target:** ^7.0 ya ^8.0 (recommended ^7.0 for minimal breaking)

**v6 → v7 Breaking Changes** [2](https://github.com/spatie/laravel-permission/blob/main/docs/upgrading.md):
- PHP 8.3+ required (already done in Phase 1)
- Laravel 12+ required (we are on 12, ok)
- Event renames: `PermissionAttached` → `PermissionAttachedEvent` etc. - Aap events use nahi karte, so safe
- Command renames: `CacheReset` → `CacheResetCommand` - artisan signature same, safe
- Return types `static` added - agar aapne trait override kiya hota to break hota, but aapne nahi kiya
- `Wildcard` contract `__construct` removed

**Steps:**
```bash
# Backup config
cp config/permission.php config/permission.php.bak

# Update composer.json
# Change: "spatie/laravel-permission": "^6.10" → "^7.0"

composer update spatie/laravel-permission --with-all-dependencies

# Compare config
diff config/permission.php vendor/spatie/laravel-permission/config/permission.php
# If new options, merge manually

# Migration check - compare stubs
# vendor/spatie/laravel-permission/database/migrations/... vs your existing migration
# 2026_07_26_151256_create_permission_tables.php
# Usually no structural change from v6 to v7, but check

php artisan migrate --dry-run
php artisan permission:cache-reset
php artisan test --filter=Permission
```

**If you go v7 → v8 (optional, for longer support):**
- Contracts signatures change PR #2953 - check if you implemented contracts (you didn't)
- Config file re-publish recommended

**Recommendation:** Pehle ^7.0 pe jao, test karo, fir ^8.0 pe jao agar time hai. Dono Laravel 13 support karte hai [4](https://laravelshift.com/can-i-upgrade-laravel/spatie/laravel-permission).

### 2.3 Other Packages Update (Laravel 12 pe hi)
```bash
composer require laravel/sanctum:^4.3.3 --with-all-dependencies
# Sanctum 4.3.3 L13 support [1](https://github.com/laravel/sanctum/releases)

composer require barryvdh/laravel-dompdf:^3.1.2 --with-all-dependencies
# 3.1.2 L13 compat [1](https://laravelshift.com/can-i-upgrade-laravel/barryvdh/laravel-dompdf)

# Dev packages - L12 pe hi latest pe le aao
composer require laravel/pint:^1.32 laravel/pail:^1.2 nunomaduro/collision:^8.8 --dev
```

**Deliverable:** Sab packages Laravel 12 pe latest compatible version pe, PHP 8.3 pe green
**Commit:** `chore: upgrade spatie/permission to v7, sanctum to 4.3.3, dompdf to 3.1.2 on L12`

---

## PHASE 3: Laravel 13 Core Upgrade (Day 2 - Morning) - 2 Hours
**Goal:** Framework ko 13 pe le jana

### 3.1 Branch & Backup
```bash
git checkout -b upgrade/laravel-13
git checkout -b backup/laravel-12-before-13 # rollback ke liye
git checkout upgrade/laravel-13

# Lock file snapshot for rollback
cp composer.lock composer.lock.laravel12.bak
```

### 3.2 composer.json Final Update
```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^13.0",
    "laravel/sanctum": "^4.3.3",
    "laravel/tinker": "^3.0",
    "barryvdh/laravel-dompdf": "^3.1.2",
    "intervention/image": "^3.11",
    "spatie/laravel-permission": "^7.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^12.0",
    "fakerphp/faker": "^1.23",
    "larastan/larastan": "^3.10",
    "laravel/pail": "^1.2.2",
    "laravel/pint": "^1.29",
    "laravel/sail": "^1.41",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.6",
    "phpstan/phpstan": "^2.2"
  },
  "config": {
    "platform": { "php": "8.3.0" }
  }
}
```

### 3.3 Composer Update
```bash
composer update --with-all-dependencies -W

# Agar conflict:
composer update laravel/framework laravel/tinker phpunit/phpunit spatie/laravel-permission --with-all-dependencies

# Clear caches
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

**Expected Output:** No errors, lock file updated to laravel/framework v13.x

### 3.4 Config Sync with L13 Skeleton
**Manual diff karo L13 skeleton se:**

```bash
# Fresh L13 skeleton download for comparison (temp)
composer create-project laravel/laravel:^13.0 /tmp/laravel13-skeleton --no-install
diff -u /tmp/laravel13-skeleton/config/cache.php config/cache.php
diff -u /tmp/laravel13-skeleton/config/session.php config/session.php
```

**Changes to apply:**

**config/cache.php - Add:**
```php
'serializable_classes' => env('CACHE_SERIALIZABLE_CLASSES', false),
// Ya temporarily true for seamless upgrade:
// 'serializable_classes' => true, // L12 behavior, later change to false
```

**config/session.php - Add:**
```php
'serialization' => env('SESSION_SERIALIZATION', 'php'), // or 'json' for hardening
// Recommendation: Start with 'php' for zero-downtime, then migrate to 'json' in next release
```

**Also check:**
- `config/app.php` - L13 me koi new keys? Compare
- `config/queue.php` - L13 default database, you already have it

### 3.5 .env.example & .env Pinning (CRITICAL - MUST DO BEFORE DEPLOY)
```env
# Add to .env.example
CACHE_PREFIX=laravel-business-starter-kit-cache-
REDIS_PREFIX=laravel-business-starter-kit-database-
SESSION_COOKIE=laravel-business-starter-kit-session
SESSION_SECURE_COOKIE=true
CACHE_SERIALIZABLE_CLASSES=false
SESSION_SERIALIZATION=php

# Production .env me bhi same pin karo BEFORE deploy
# Isse cache invalid aur logout issue nahi hoga [7](https://ortamarco.me/en/blog/what-breaks-upgrading-to-laravel-13/)
```

**Deliverable:** App locally Laravel 13 pe boot ho raha hai, `php artisan about` shows L13
**Commit:** `feat: upgrade to Laravel 13.0, sync config`

---

## PHASE 4: Code Adaptation & Hardening (Day 2 - Afternoon) - 1 Hour
**Goal:** Low-impact breaking changes fix karna

### 4.1 Grep & Fix Checklist
```bash
# CSRF middleware rename check (should be 0)
grep -rn "VerifyCsrfToken\|ValidateCsrfToken" app/ tests/ routes/ bootstrap/ --include="*.php"

# QueueBusy property rename
grep -rn "QueueBusy" app/ --include="*.php"
# If found: $connection → $connectionName

# array_first / array_last global helpers
grep -rn "array_first\|array_last" app/ --include="*.php"
# If found, use Illuminate\Support\Arr::first() instead (polyfill conflict)

# upsert empty uniqueBy
grep -rn "upsert" app/ --include="*.php" -A2 -B2
# Ensure uniqueBy not empty

# Model booting instantiation
grep -rn "new.*Model\|new User\|new Media" app/Models app/Traits --include="*.php" -A2 -B5
# Ensure no model instantiation inside boot() methods
```

### 4.2 Optional Improvements (Recommended)
**WelcomeNotification & PasswordChangedNotification me:**
```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels; // L13 new

#[DeleteWhenMissingModels]
final class WelcomeNotification extends Notification implements ShouldQueue
{
    // ...
}
```
Ye ensure karega ki agar user delete ho gaya to queued notification fail nahi hoga, delete ho jayega [5](https://laravel.com/docs/13.x/upgrade).

**DashboardService me:**
```php
// Currently arrays cache karte ho, good
// Agar future me objects cache karoge to allow list banao:
'serializable_classes' => [
    \App\Support\StatisticCard::class,
    \Illuminate\Database\Eloquent\Collection::class,
]
```

### 4.3 Frontend Build
```bash
npm install
npm run build
# Check for Vite errors
```

**Deliverable:** Code L13 compatible, no grep hits for risky patterns
**Commit:** `refactor: L13 code adaptations, add DeleteWhenMissingModels`

---

## PHASE 5: Testing & QA (Day 2 - Evening to Day 3 - Morning) - 4-6 Hours
**Goal:** Full test coverage, zero regression

### 5.1 Automated Tests
```bash
# Unit & Feature
php artisan test
# Or
vendor/bin/phpunit

# Static Analysis
vendor/bin/phpstan analyse --level=8
vendor/bin/pint --test

# Fresh DB test
php artisan migrate:fresh --seed --env=testing
php artisan test --env=testing

# Check seeded accounts
# admin@example.com / Password!2345 should work
```

**Expected Failures & Fixes:**
- PHPUnit 12 me some assertions deprecated → update tests
- `Str::` factories reset between tests → if tests fail, set factories in setUp()
- Cache with `serializable_classes=false` → if any test caches objects, it will return Incomplete_Class → fix by adding to allow list or using arrays

### 5.2 Manual Smoke Test Checklist (Critical)
- [ ] Registration: `/register` → new user, email verification log me check
- [ ] Login: `admin@example.com` → dashboard
- [ ] Login with wrong password → failed login history
- [ ] Inactive user login → 403
- [ ] Dashboard stats: total_users, active_users, verified_users, successful_logins
- [ ] Profile update: name, avatar upload (intervention/image test)
- [ ] Password change: old password required, session invalidation
- [ ] Theme toggle: dark/light mode
- [ ] Media upload: avatar, conversion thumbnails generation
- [ ] Roles & Permissions: `permission:dashboard.view` middleware test
- [ ] API: `/api/v1/login` → token, `/api/v1/me` with Bearer token
- [ ] Session persistence: login → refresh → still logged in (cookie name test)
- [ ] Cache: dashboard metrics cached, `Cache::forget` works
- [ ] Queue: Register user → `SendWelcomeNotification` queued → process with `php artisan queue:work`
- [ ] Security headers: Check `X-Frame-Options`, `CSP` nonce present

### 5.3 Performance & Logs
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check logs
tail -f storage/logs/laravel.log
```

**Deliverable:** All tests green, manual QA passed
**Commit:** `test: fix PHPUnit 12 compat, verify L13 smoke tests`

---

## PHASE 6: Staging Deployment (Day 3 - Afternoon) - 2 Hours
**Goal:** Production-like environment pe test

### 6.1 Staging Deploy Steps
```bash
# On staging server (PHP 8.3 already)
git pull origin upgrade/laravel-13
composer install --no-dev --optimize-autoloader
npm run build

# Critical: Pin prefixes BEFORE deploy (if not already in .env)
# Check .env has CACHE_PREFIX, SESSION_COOKIE etc.

# Additive migrations only (no drops/renames in same deploy)
php artisan migrate --force --isolated # if multiple servers

# Clear caches inside new release BEFORE symlink swap
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Restart workers
php artisan queue:restart
# If using Horizon: php artisan horizon:terminate
# If Octane: php artisan octane:reload
# FPM reload: sudo systemctl reload php8.3-fpm

# No php artisan down - zero downtime
```

### 6.2 Staging QA
- [ ] Run same manual smoke tests as Phase 5.2 on staging URL
- [ ] Test with real data volume (if possible)
- [ ] Monitor: Sentry/Flare/Bugsnag for 2-4 hours
- [ ] Check queue jobs: L12 queued jobs L13 worker pe deserialize hote hai ya nahi? [1](https://laravel-vuejs.com/2026/laravel-13-vue-upgrade-guide/)
- [ ] Cache hit/miss monitoring
- [ ] Session: existing users logged out? (Should NOT if prefixes pinned)

**Deliverable:** Staging stable, no errors for 4 hours

---

## PHASE 7: Production Rollout & Rollback Plan (Day 3 - Evening) - 1 Hour + Monitoring
**Goal:** Zero-downtime production deploy

### 7.1 Pre-Production Checklist
- [ ] Production PHP verified 8.3+ on BOTH CLI and FPM (`php -v` and `phpinfo()` via web)
- [ ] `composer why-not laravel/framework 13.0` returns nothing
- [ ] `composer audit` clean
- [ ] Full test suite green in CI on PHP 8.3
- [ ] Lock file diff reviewed
- [ ] Rollback branch ready: `backup/laravel-12-before-13` with old composer.lock
- [ ] Database backup taken
- [ ] Error tracker open in tab
- [ ] Maintenance window announced (even though zero-downtime, inform)

### 7.2 Production Deploy (Atomic Release Strategy) [4](https://dev.to/deploynix/upgrading-to-laravel-13-in-production-with-zero-downtime-2l4n)
```bash
# Same as staging, but with extra care

# 1. Deploy code to new release folder (e.g., /var/www/releases/20260916-l13)
# 2. Inside new release:
composer install --no-dev --optimize-autoloader --no-interaction
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Symlink swap (atomic)
ln -sfn /var/www/releases/20260916-l13 /var/www/current

# 4. Migrate
php artisan migrate --force --isolated

# 5. Restart workers
php artisan queue:restart
sudo systemctl reload php8.3-fpm
# If Horizon: horizon:terminate
# If Octane: octane:reload

# 6. Clear old caches
php artisan optimize:clear # on old release? Actually new release already cleared

# 7. Monitor
# - Sentry for 30 mins
# - Logs: tail -f storage/logs/laravel.log
# - Queue: php artisan queue:monitor
# - Uptime: Check /up health endpoint
```

### 7.3 Rollback Plan (If Something Breaks)
**Code Rollback (90% cases me kaafi hai):**
```bash
# Point symlink back to previous release
ln -sfn /var/www/releases/20260915-l12 /var/www/current
sudo systemctl reload php8.3-fpm
php artisan queue:restart
php artisan optimize:clear
```

**Database Rollback (Only if migration was destructive - but we said additive only):**
```bash
# Restore DB backup
# php artisan migrate:rollback (only if safe)
```

**Lock File Rollback:**
```bash
git checkout backup/laravel-12-before-13
cp composer.lock.laravel12.bak composer.lock
composer install --no-dev
```

**When to Rollback?**
- Error rate > 5% for 5 mins
- Queue jobs failing continuously
- Login completely broken
- Payment/media upload broken

### 7.4 Post-Deploy Monitoring (48 Hours)
- [ ] Error tracker: Check every 30 mins for first 4 hours, then daily
- [ ] Logs: `storage/logs/laravel.log` me `InvalidArgumentException` for upsert, `LogicException` for model booting, `QueryException` for DELETE JOIN
- [ ] Queue: Failed jobs table check `php artisan queue:failed`
- [ ] Cache: Hit rate normal?
- [ ] Sessions: Users complaining about logout? (If pinned correctly, no)
- [ ] Performance: Response time same or better? PHP 8.3 faster hai

**Deliverable:** Production on L13, stable for 48 hours

---

## POST-UPGRADE: Adoption of New Features (Week 2 onwards - Optional)
**Goal:** L13 ke naye features incrementally adopt karna, big bang nahi

### Optional Improvements:
1. **PHP Attributes for Middleware:**
   ```php
   // Old:
   Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view');
   
   // New L13 style (optional):
   use Illuminate\Routing\Attributes\Middleware;
   #[Middleware('permission:dashboard.view')]
   class DashboardController extends Controller {}
   ```

2. **Cache::touch():**
   ```php
   // Old: fetch + put
   // New:
   Cache::touch('dashboard.metrics', 300); // extends TTL without fetching
   ```

3. **Session JSON Hardening:**
   - Change `SESSION_SERIALIZATION` from `php` to `json` in next minor release (will logout all users, so announce)

4. **Typed Config Helpers:** L13 me `config()` typed helpers - use where beneficial

5. **Passkeys / AI SDK:** If needed for future features

**Rule:** Week 1 me sirf stability, Week 2+ me new features [1](https://laravel-vuejs.com/2026/laravel-13-vue-upgrade-guide/)

---

## Timeline Summary

| Phase | Duration | When | Owner |
|-------|----------|------|-------|
| 0 Audit | Done | Day 0 | You |
| 1 PHP 8.3 Env | 2h | Day 1 Morning | DevOps + Dev |
| 2 Package Blockers | 3h | Day 1 Afternoon | Dev |
| 3 Core L13 Upgrade | 2h | Day 2 Morning | Dev |
| 4 Code Adaptation | 1h | Day 2 Afternoon | Dev |
| 5 Testing & QA | 4-6h | Day 2 Eve - Day 3 Morn | Dev + QA |
| 6 Staging | 2h | Day 3 Afternoon | DevOps + QA |
| 7 Production | 1h + 48h monitor | Day 3 Evening | DevOps + Dev |
| **Total** | **~15-17h work + 48h monitoring** | **3 Days** | |

---

## Commands Cheat Sheet (Copy-Paste Ready)

```bash
# Phase 1
php -v
composer why-not laravel/framework ^13.0

# Phase 2
composer require spatie/laravel-permission:^7.0 --with-all-dependencies
composer require laravel/sanctum:^4.3.3 barryvdh/laravel-dompdf:^3.1.2 --with-all-dependencies
php artisan permission:cache-reset

# Phase 3
git checkout -b upgrade/laravel-13
composer require laravel/framework:^13.0 laravel/tinker:^3.0 phpunit/phpunit:^12.0 --with-all-dependencies -W
php artisan optimize:clear

# Phase 4
grep -rn "VerifyCsrfToken\|ValidateCsrfToken\|QueueBusy\|array_first" app/ tests/ --include="*.php"
npm run build

# Phase 5
php artisan test
php artisan migrate:fresh --seed
vendor/bin/phpstan analyse

# Phase 6 & 7
composer install --no-dev --optimize-autoloader
php artisan migrate --force --isolated
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
sudo systemctl reload php8.3-fpm
```

---

## Risk Mitigation Summary

| Risk | Mitigation | Phase |
|------|------------|-------|
| Users logout / cache invalid | Pin CACHE_PREFIX, SESSION_COOKIE BEFORE deploy | 3.5 |
| Permission package breaking | Upgrade to v7 first on L12, test thoroughly | 2.2 |
| PHP 8.2 server fail | Upgrade PHP separately, soak 1 week | 1 |
| Object cache break | Start with `serializable_classes=true`, then false + allow list | 3.4 |
| Queue jobs fail | Drain queues before deploy, test serialize/deserialize | 6.2 |
| No rollback | Keep backup branch + lock file + DB backup | 3.1 & 7.3 |

---

## Final Decision Tree

```
Is production on PHP 8.3+?
├─ No → Phase 1: Upgrade PHP first, run 1 week on L12 + PHP 8.3
└─ Yes → Is spatie/permission v7+ on L12 green?
         ├─ No → Phase 2: Upgrade permission on L12, test
         └─ Yes → Phase 3: Upgrade to L13
```

**Recommendation:** Aapka case me PHP 8.2 hai, to **Phase 1 → Phase 2 → Phase 3** sequence follow karo. Direct L13 pe jump mat karo.

---

**Next Action:** Jab aap bolo, main Phase 1 se start kar dunga - pehle PHP version check, fir permission upgrade. Bolo kab start karna hai?
