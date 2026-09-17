# PHASE 5 Report: Testing & QA

**Date:** 2026-09-16
**Branch:** arena/01a0aa83-starterkitfree
**Status:** Test plan created, static review done, runtime verification pending on local
**Previous:** Phase 4 (Code Adaptation) ✅

---

## Goal
Laravel 13 upgrade ke baad full regression testing karna - automated + manual + performance + security.

---

## Automated Tests Review (Static)

### Existing Test Suite
**Location:** `tests/`

**Feature Tests:**
1. **AuthenticationTest.php** (6 tests)
   - `a_visitor_can_register_through_the_api`
   - `registration_rejects_a_duplicate_email`
   - `an_active_user_receives_a_token_on_login`
   - `the_issued_token_grants_access_to_protected_endpoints`
   - `login_is_refused_for_an_inactive_user`
   - `login_is_refused_for_an_incorrect_password`
   - Uses: `RefreshDatabase`, `Notification::fake`, `Password::$defaultCallback`, `#[Test]`
   - **PHPUnit 12 Compat:** ✅ - Uses attributes, modern assertions, no deprecated methods

2. **HomeRedirectTest.php** (4 tests)
   - `the_root_redirects_to_the_dashboard`
   - `guests_reaching_the_dashboard_are_sent_to_sign_in`
   - `the_sign_in_screen_is_reachable`
   - `the_health_endpoint_reports_the_edition`
   - **Compat:** ✅ Simple

3. **SecurityHeadersTest.php** (8 tests)
   - `web_responses_carry_the_security_headers`
   - `api_responses_carry_the_security_headers`
   - `only_one_content_security_policy_header_is_emitted`
   - `the_policy_locks_down_the_dangerous_directives`
   - `inline_scripts_are_allowed_only_through_a_nonce`
   - `every_inline_script_carries_the_header_nonce`
   - `the_nonce_is_regenerated_for_every_request`
   - `hsts_is_absent_on_plain_http`
   - `hsts_is_applied_to_secure_requests`
   - Uses: `RefreshDatabase`, `withoutVite()`
   - **Compat:** ✅ - CSP nonce tests important for L13 (no change, but verify)

**Unit Tests:**
1. **MediaUrlTest.php** (5 tests)
   - `it_builds_local_urls_from_the_current_request_host`
   - `it_honours_a_custom_domain`
   - `it_preserves_a_custom_public_path_prefix`
   - `it_tolerates_a_leading_slash_on_the_path`
   - `it_delegates_remote_disks_to_the_filesystem`
   - Uses: `Config::set`, `Storage::shouldReceive` (Mockery)
   - **Compat:** ✅

**Total:** ~18 tests

### phpunit.xml Review
```xml
<env name="APP_ENV" value="testing"/>
<env name="CACHE_STORE" value="array"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
```
- Uses array cache, sqlite memory, array session, sync queue - good for testing
- **L13 Impact:** 
  - `CACHE_STORE=array` with `serializable_classes=false` - array store doesn't serialize, so safe
  - `SESSION_DRIVER=array` with `serialization=php` - array driver doesn't serialize to json, safe
  - **No changes needed**

### Potential PHPUnit 11 → 12 Breaking
**PHPUnit 12 removed:**
- `assertFileNotExists` (use `assertFileDoesNotExist`)
- `assertDirectoryNotExists` (use `assertDirectoryDoesNotExist`)
- Some deprecated annotations

**Your tests:** Use only modern methods (`assertOk`, `assertRedirect`, `assertJsonPath`, `assertDatabaseHas`, etc.) - **No deprecated usage found** ✅

**However, watch for:**
- `Password::$defaultCallback = null` - In L13, password defaults might have changed? Check StrongPassword rules still work
- `Notification::fake()` - Should still work in L13
- `RefreshDatabase` - Should work, but ensure migrations run with L13 (permission tables, etc.)

---

## Manual Smoke Test Checklist (Critical - Must Do Locally)

### 1. Environment Verification
- [ ] `php -v` → 8.3.x
- [ ] `php artisan --version` → Laravel 13.x
- [ ] `php artisan about` → Shows L13, PHP 8.3, correct env
- [ ] `composer show laravel/framework` → v13.x
- [ ] `composer show spatie/laravel-permission` → v7.x
- [ ] `composer show laravel/sanctum` → v4.3.3+
- [ ] `node -v` → 18/20/22 LTS
- [ ] `npm run build` → Success (already verified in Phase 4)

### 2. Database & Migrations
- [ ] `php artisan migrate:fresh --seed` → Success, no errors
- [ ] Check tables: users, cache, jobs, personal_access_tokens, login_histories, media, roles, permissions, etc.
- [ ] Seeded accounts:
  - admin@example.com / Password!2345 → Super Admin
  - manager@example.com / Password!2345 → Manager
  - viewer@example.com / Password!2345 → Viewer
- [ ] `php artisan permission:cache-reset` → Works (v7 command)

### 3. Authentication Flows (Most Critical)
- [ ] **Registration API:** POST /api/v1/register
  - New user Meera Nair, terms true → 201, success true, user.email correct, full_name correct
  - Check DB: users table has Meera, role Viewer assigned, password hashed
  - Check no password leak in response
- [ ] **Duplicate Email:** Register with taken@example.com → 422, email validation error
- [ ] **Login Active User:** POST /api/v1/login with active@example.com / Password!2345 → 200, token not empty, personal_access_tokens table has entry, login_histories successful
- [ ] **Token Access:** GET /api/v1/me with Bearer token → 200, email correct
- [ ] **Inactive User:** Login with suspended user → 403, message auth.account_inactive, no token minted
- [ ] **Wrong Password:** Login with wrong password → 422, email validation error, login_histories failed
- [ ] **Web Login:** GET /login → 200, see heading, POST login with admin@example.com → redirect to /dashboard
- [ ] **Email Verification:** MAIL_MAILER=log, check storage/logs/laravel.log for verification URL, click → verified

### 4. Dashboard (Cache Testing)
- [ ] Login as admin → /dashboard → 200
- [ ] Check statistics cards: total_users, active_users, verified_users, successful_logins
- [ ] Check registration trend chart (14 days) - uses Cache::remember with key `dashboard.registration_trend.14`
- [ ] Check authentication summary - Cache::remember `dashboard.authentication_summary`
- [ ] Check system status - Cache::remember `dashboard.system_status`
- [ ] Check recent activity and recent users
- [ ] Test permission: Viewer should see only 2 cards, Manager/Admin sees 4
- [ ] Test cache invalidation: Create new user, check if dashboard metrics update after TTL or after event FlushDashboardCache

### 5. Profile & Security
- [ ] Profile view: /profile → shows user info
- [ ] Profile update: Change first_name, last_name → success, ProfileUpdated event, dashboard cache flushed
- [ ] Avatar upload: Upload image → MediaService store, intervention/image conversion, thumbnails generated, avatar_url correct
- [ ] Avatar with leading slash tolerance (MediaUrlTest)
- [ ] Custom domain handling (MediaUrlTest)
- [ ] Password change: Change password → PasswordChanged event, SendPasswordChangedNotification queued, session invalidation if config true
- [ ] Security: Session revoke other devices
- [ ] Login history: View own login histories

### 6. Roles & Permissions (Spatie v7 Critical)
- [ ] `php artisan permission:show` → Lists all permissions and roles
- [ ] Admin has all permissions (PermissionEnum::values())
- [ ] Manager has 11 permissions (DashboardView, ProfileView, etc.)
- [ ] Viewer has 7 permissions
- [ ] Test middleware: `permission:dashboard.view` - Viewer can access dashboard, guest cannot
- [ ] Test `hasRole` and `can` methods on User model
- [ ] Test `Role::findOrCreate` and `Permission::findOrCreate` still work (seeder)
- [ ] Test `syncPermissions` and `assignRole` return type static (should not break)

### 7. Media System (Intervention/Image)
- [ ] Upload image via profile avatar → Media table entry, file stored in storage/app/public/media/avatar/
- [ ] Check conversions: thumbnails generated (from config/media.php)
- [ ] Check MediaUrl::resolve builds correct URL from current request host (127.0.0.1 vs localhost)
- [ ] Check isImage() and humanReadableSize()
- [ ] Delete media → file and conversions deleted, DB entry deleted

### 8. Security Headers (SecurityHeadersTest)
- [ ] Web response has: X-Frame-Options SAMEORIGIN, X-Content-Type-Options nosniff, Referrer-Policy, Cross-Origin-Opener-Policy, Permissions-Policy
- [ ] Only one CSP header emitted
- [ ] CSP has default-src 'self', object-src 'none', base-uri 'self', etc.
- [ ] CSP script-src has nonce, no unsafe-inline
- [ ] Every inline script has nonce attribute matching header
- [ ] Nonce regenerated every request
- [ ] HSTS absent on http, present on https with max-age=31536000
- [ ] Test with `withoutVite()` still works (from test setUp)

### 9. API & Sanctum
- [ ] POST /api/v1/register → 201
- [ ] POST /api/v1/login → token
- [ ] GET /api/v1/me with Bearer → 200
- [ ] GET /api/v1/dashboard with Bearer and permission → 200
- [ ] GET /api/v1/health → success true, status ok
- [ ] Test token expiration if configured in sanctum.php

### 10. Session & Cache (L13 Critical)
- [ ] **Session Cookie Name:** Check browser dev tools → cookie name should be `laravel-business-starter-kit-session` (pinned)
- [ ] **Session Persistence:** Login → refresh → still logged in (no logout)
- [ ] **Cache Prefix:** Check cache table keys → should have prefix `laravel-business-starter-kit-cache-`
- [ ] **Serializable Classes:** 
  - With `CACHE_SERIALIZABLE_CLASSES=false`, try to cache an object → should get Incomplete_Class or error
  - Your current code caches arrays → should work
  - Try `Cache::put('test', new stdClass(), 60)` → should fail or return Incomplete_Class when get
- [ ] **Session Serialization:**
  - With `SESSION_SERIALIZATION=php`, session should work seamlessly
  - Change to `json` in .env, clear config, login → should work but old sessions invalidated (users logout) - test in staging

### 11. Queue & Notifications (L13 Improvement)
- [ ] Register new user → `SendWelcomeNotification` queued (ShouldQueue)
- [ ] `php artisan queue:work` → processes welcome mail (logged)
- [ ] Check `#[DeleteWhenMissingModels]`:
  - Create user, dispatch WelcomeNotification, delete user before queue processes → job should be deleted, not failed
- [ ] Password change → `SendPasswordChangedNotification` queued

### 12. Frontend (Already Verified in Phase 4)
- [x] `npm install` → 101 packages
- [x] `npm run build` → vite 7.3.6, 66 modules, success
- [ ] `npm run dev` → Vite dev server runs, HMR works, CSP allows localhost:5173
- [ ] Check Alpine.js directives work (requires unsafe-eval in CSP, which you have)
- [ ] Check ApexCharts, Flatpickr, SortableJS work

### 13. Edge Cases & Security
- [ ] SQL Injection: Try `' OR '1'='1` in search → should be escaped
- [ ] XSS: Try `<script>alert(1)</script>` in first_name → should be escaped in Blade
- [ ] CSRF: Try POST without token → 419, but your exception handler redirects to login with session_expired message
- [ ] Rate Limiting: Try 6 failed logins → lockout 15 mins (AUTH_LOCKOUT_MAX_ATTEMPTS=5)
- [ ] Strong Password: Try weak password → validation fails, requires uppercase, lowercase, numbers, symbols, min 10
- [ ] Inactive user cannot login → 403
- [ ] Locked account cannot login → 429

---

## Performance Checks

- [ ] `php artisan optimize` → Success
- [ ] `php artisan config:cache` + `route:cache` + `view:cache` → Success, app still works
- [ ] Response time: Compare L12 vs L13 - should be similar or better (PHP 8.3 faster)
- [ ] Memory usage: Check with `memory_get_peak_usage()` - should be similar
- [ ] Dashboard with cache: First load slow (DB queries), second load fast (cache hit)
- [ ] N+1 queries: Check with `Model::shouldBeStrict()` - should throw if N+1 detected in non-production

---

## Security Checks (L13 Hardening)

- [ ] `CACHE_SERIALIZABLE_CLASSES=false` → Prevents gadget chain attacks if APP_KEY leaked
- [ ] `SESSION_SERIALIZATION=json` (future) → Prevents object injection in session
- [ ] `PreventRequestForgery` middleware → Checks Sec-Fetch-Site header (origin verification)
- [ ] `SESSION_SECURE_COOKIE=true` in .env.example → Secure cookies
- [ ] CSP nonce regenerated every request → Prevents replay
- [ ] HSTS only on secure requests → Correct
- [ ] No password leak in API responses → Check

---

## Test Commands for Local

```bash
# Automated
php artisan test
# Should be ~18 tests, all green

# With coverage (if xdebug/pcov)
php artisan test --coverage

# Static analysis
vendor/bin/phpstan analyse --level=8
vendor/bin/pint --test

# Fresh DB
php artisan migrate:fresh --seed
php artisan test --env=testing

# Frontend
npm run build
npm run dev # in separate terminal

# Queue
php artisan queue:work --tries=1

# Logs
tail -f storage/logs/laravel.log
```

---

## Known Issues to Watch (From L13 Upgrade Guide)

1. **Cache Invalid / Logout:** If CACHE_PREFIX, SESSION_COOKIE not pinned → will happen. We pinned in .env.example, so should be safe, but verify.
2. **Serializable Classes:** If you cache objects (you don't), will break with false. Test by caching stdClass.
3. **Session JSON:** If you switch to json, all users logout. We keep php for now, so seamless.
4. **Permission v7 Return Types:** Methods now return static, but you don't check return, so safe. Verify seeder works.
5. **PHPUnit 12:** Some assertions removed, but your tests use modern ones, should be safe.
6. **Queue Job Serialization:** L12 queued jobs may fail on L13 worker. Drain queues before deploy.

---

## Files Reviewed

- `tests/Feature/AuthenticationTest.php` - 6 tests, PHPUnit 12 compat ✅
- `tests/Feature/HomeRedirectTest.php` - 4 tests ✅
- `tests/Feature/SecurityHeadersTest.php` - 8 tests ✅
- `tests/Unit/Support/MediaUrlTest.php` - 5 tests ✅
- `phpunit.xml` - array cache, sqlite memory, safe for L13 ✅
- `config/cache.php` - serializable_classes added ✅
- `config/session.php` - serialization added ✅

---

## Next Steps - Phase 6 & 7

**Phase 6: Staging Deploy (2h)**
- Deploy to staging with PHP 8.3 + L13
- Run all manual smoke tests on staging URL
- Monitor Sentry/Logs 4h

**Phase 7: Production Rollout (1h + 48h monitoring)**
- Zero-downtime deploy (atomic symlink)
- Rollback plan ready
- Monitor 48h

---

## Verification Checklist (For You)

- [ ] `php artisan test` → ~18 tests green
- [ ] `vendor/bin/phpstan analyse` → No errors
- [ ] `vendor/bin/pint --test` → No style issues
- [ ] Manual smoke tests (13 sections above) → All passed
- [ ] Frontend build → Success (already verified)
- [ ] Performance → Similar or better than L12
- [ ] Security → Hardening verified

---

**Phase 5 Status:** ✅ Test plan created, static review done, ⏳ Runtime verification pending on your local (needs PHP 8.3 + L13)
**Overall Progress:** 5/7 phases complete (71%) - Testing plan ready!
**Next:** Phase 6 Staging Deploy
