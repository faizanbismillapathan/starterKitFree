# PHASE 2 Report: Dependency Blocker Resolution (Laravel 12 + PHP 8.3)

**Date:** 2026-09-16
**Branch:** arena/01a0aa83-starterkitfree
**Status:** File changes done, static checks passed, runtime verification pending on local

---

## Goal
`composer why-not laravel/framework ^13.0` ko green karna - saare packages jo L13 ko block kar rahe hai unko L12 pe hi latest L13-compatible version pe le aana.

---

## Changes Kiye

### composer.json
**Before (Phase 1 ke baad):**
```json
"barryvdh/laravel-dompdf": "^3.1",
"laravel/sanctum": "^4.3",
"spatie/laravel-permission": "^6.10"
```

**After (Phase 2):**
```json
"barryvdh/laravel-dompdf": "^3.1.2",
"laravel/sanctum": "^4.3.3",
"spatie/laravel-permission": "^7.0"
```

**Reason:**
- `spatie/laravel-permission ^6.10` → L13 support nahi karta. L13 ke liye `7.2.1 - 8.3.0` chahiye [4](https://laravelshift.com/can-i-upgrade-laravel/spatie/laravel-permission). Hum `^7.0` pe ja rahe hai jo 7.2.1+ include karta hai.
- `laravel/sanctum ^4.3` → `^4.3.3` me L13 support add hua PR #587 via [1](https://github.com/laravel/sanctum/releases)
- `barryvdh/laravel-dompdf ^3.1` → `^3.1.2` me L13 compat [1](https://laravelshift.com/can-i-upgrade-laravel/barryvdh/laravel-dompdf)

**Other packages unchanged for Phase 2:**
- `laravel/framework: ^12.0` (still L12, Phase 3 me ^13.0 hoga)
- `laravel/tinker: ^2.10.1` (Phase 3 me ^3.0)
- `phpunit: ^11.5.50` (Phase 3 me ^12.0)
- `php: ^8.3`, `platform: 8.3.0` (Phase 1 se)

---

## Deep Dive: Spatie Permission v6 → v7

### Official Breaking Changes (from docs/upgrading.md)
**Requirements:**
- PHP 8.3+ (Phase 1 me done)
- Laravel 12+ (we are on 12)

**Event Class Renames:**
| v6 | v7 |
|---|---|
| `PermissionAttached` | `PermissionAttachedEvent` |
| `PermissionDetached` | `PermissionDetachedEvent` |
| `RoleAttached` | `RoleAttachedEvent` |
| `RoleDetached` | `RoleDetachedEvent` |

**Command Class Renames:**
| v6 | v7 |
|---|---|
| `CacheReset` | `CacheResetCommand` |
| `CreateRole` | `CreateRoleCommand` |
| `CreatePermission` | `CreatePermissionCommand` |
| `Show` | `ShowCommand` |
| etc. | |

**Type Hints:**
- `HasPermissions::givePermissionTo()`, `syncPermissions()`, `revokePermissionTo()` now return `static` instead of `self`
- `HasRoles::assignRole()`, `removeRole()`, `syncRoles()` now return `static`
- `PermissionRegistrar::forgetCachedPermissions()` now returns `bool`

**Removed:**
- `PermissionRegistrar::clearClassPermissions()` removed, use `clearPermissionsCollection()`

### Aapke Codebase Pe Impact Analysis (Static Checks)

**Check 1: Event Listeners**
```bash
grep -R "PermissionAttached|PermissionDetached|RoleAttached|RoleDetached" app/
```
**Result:** 0 matches → **SAFE** - Aap events use nahi karte

**Check 2: Command References**
```bash
grep -R "CacheReset|CreateRole|CreatePermission" app/
```
**Result:** 0 matches → **SAFE** - Aap commands directly use nahi karte

**Check 3: clearClassPermissions**
```bash
grep -R "clearClassPermissions" app/
```
**Result:** 0 matches → **SAFE** - Aap `forgetCachedPermissions()` use karte ho

**Check 4: HasRoles Overrides**
```bash
grep -R "function.*givePermissionTo|function.*assignRole|function.*syncPermissions" app/
```
**Result:** 0 matches → **SAFE** - Aap trait override nahi karte

**Check 5: Custom Role/Permission Models**
```bash
grep -R "extends.*Role|extends.*Permission" app/
```
**Result:** 0 matches → **SAFE** - Aap custom models nahi banaye

**Check 6: RolePermissionSeeder**
- Uses `Role::findOrCreate()`, `Permission::findOrCreate()`, `syncPermissions()`, `forgetCachedPermissions()`
- In v7, `findOrCreate` and `syncPermissions` return `static`, but aap return value check nahi karte, sirf call karte ho → **SAFE**
- `forgetCachedPermissions()` now returns bool, but aap return value ignore karte ho → **SAFE**

**Check 7: Config Compatibility**
- v6.10 config vs v7.0 config diff: **No structural change** (only comments about Event suffix in v8)
- v7.2.1 (first L13 compatible) config same as v6
- v8.0.0 adds `team` and `default_model` keys, but we are going to v7, not v8 → **SAFE**, no config change needed

**Final Verdict for Permission Upgrade:** ✅ **SAFE** - Zero breaking impact on your codebase

### Sanctum & Dompdf Impact
- **Sanctum 4.3 → 4.3.3:** Only L13 support added, no breaking changes. Your `HasApiTokens` trait and `personal_access_tokens` table unchanged → **SAFE**
- **Dompdf 3.1 → 3.1.2:** L13 compat, new config options `allowedRemoteHosts` etc., but your current config not published, so default secure values used → **SAFE**

---

## Static Checks Performed (Apne Level Par)

- [x] composer.json updated and validated (JSON valid)
- [x] Grep for old event classes - 0 found
- [x] Grep for old command classes - 0 found
- [x] Grep for removed methods - 0 found
- [x] Grep for trait overrides - 0 found
- [x] Config file compatibility checked via git clone of spatie repo (v6.10, v7.0, v7.2.1, v8.0.0)
- [x] Migration file compatibility checked - no structural change from v6 to v7
- [x] RolePermissionSeeder logic reviewed - safe for v7 return types
- [x] User model HasRoles usage reviewed - safe
- [x] No custom Role/Permission models - safe

---

## Local Machine Pe Kya Karna Hai (Aapke Liye)

### Step 1: Composer Update (IMPORTANT - L12 pe hi)
```bash
cd starterKitFree
# Ensure PHP 8.3
php -v

# Backup
cp composer.json composer.json.bak
cp composer.lock composer.lock.bak

# Update only the 3 packages first (safer)
composer update spatie/laravel-permission laravel/sanctum barryvdh/laravel-dompdf --with-all-dependencies

# If success, then full update
composer update --with-all-dependencies

# Check for errors
# If you see "Your requirements could not be resolved", run:
composer why-not spatie/laravel-permission 7.0
```

### Step 2: Config & Cache Clear
```bash
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset
# Should work with v7

php artisan migrate --dry-run
# Check if any new migration needed (usually not for v6->v7)
```

### Step 3: Tests
```bash
php artisan test
# All should be green

# Specifically test permission
php artisan test --filter=Permission
php artisan test --filter=Role
```

### Step 4: Manual Smoke Tests (Critical for Permission)
- [ ] Login as admin@example.com
- [ ] Check dashboard loads (requires permission:dashboard.view)
- [ ] Check if Manager role sees correct menu items
- [ ] Check if Viewer role sees limited menu
- [ ] Create new user via CreateUserAction, check default role Viewer assigned
- [ ] Check `php artisan permission:show` works (new command class but same signature)
- [ ] Check `Role::findOrCreate` still works via seeder: `php artisan db:seed --class=RolePermissionSeeder`

### Step 5: Commit composer.lock
```bash
git add composer.json composer.lock
git commit -m "phase2: upgrade permission to ^7.0, sanctum to ^4.3.3, dompdf to ^3.1.2 on L12"
```

---

## Files Changed

1. **composer.json**
   - `barryvdh/laravel-dompdf: ^3.1 → ^3.1.2`
   - `laravel/sanctum: ^4.3 → ^4.3.3`
   - `spatie/laravel-permission: ^6.10 → ^7.0`

2. **Documentation:**
   - `PHASE_2_REPORT.md` (this file)

**No other files changed** - config/permission.php unchanged (compatible)

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Permission package breaking | Low (0 grep hits) | High | Static checks done, manual smoke tests on local required |
| Sanctum breaking | Very Low | Medium | Minor version bump only, L13 compat |
| Dompdf breaking | Very Low | Low | Minor version bump, no published config |
| Composer conflict | Medium | Medium | Run `composer update` with --with-all-dependencies, check why-not |

---

## Next Steps - Phase 3 Preview

Phase 3 me core Laravel 13 upgrade hoga:
```json
"laravel/framework": "^12.0 → ^13.0",
"laravel/tinker": "^2.10.1 → ^3.0",
"phpunit/phpunit": "^11.5.50 → ^12.0"
```

Plus config sync:
- `config/cache.php` add `serializable_classes`
- `config/session.php` add `serialization`
- `.env.example` add `CACHE_PREFIX`, `SESSION_COOKIE` pins

**Phase 3 tab start karo jab:**
- [ ] Phase 2 composer update local pe successful
- [ ] All tests green on L12 + PHP 8.3 + Permission v7
- [ ] Manual permission smoke tests passed
- [ ] composer.lock committed

---

## Verification Checklist (For You to Tick)

- [ ] `composer.json` shows new versions
- [ ] `composer update spatie/laravel-permission laravel/sanctum barryvdh/laravel-dompdf --with-all-dependencies` successful
- [ ] `php artisan permission:cache-reset` works
- [ ] `php artisan test` green
- [ ] Manual login + permission checks passed
- [ ] `composer.lock` updated and committed

---

**Phase 2 Status:** ✅ File changes done, static checks passed, ⏳ Runtime verification pending on your local
**Next:** Phase 3 - Laravel 13 Core Upgrade (after you verify Phase 2 locally)
