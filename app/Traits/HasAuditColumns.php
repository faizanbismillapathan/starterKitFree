<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Maintains the documented audit columns (07_Database_Architecture.md §7).
 *
 * The trait keeps `created_by`, `updated_by` and `deleted_by` synchronised with
 * the authenticated user without requiring per-model boilerplate.
 */
trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(static function (self $model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            $model->created_by ??= $userId;
            $model->updated_by ??= $userId;
        });

        static::updating(static function (self $model): void {
            if ($userId = Auth::id()) {
                $model->updated_by = $userId;
            }
        });

        static::deleting(static function (self $model): void {
            $userId = Auth::id();

            if ($userId === null || ! static::usesSoftDeletes()) {
                return;
            }

            $model->deleted_by = $userId;
            $model->saveQuietly();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Detects soft delete support without forcing the trait onto every model.
     */
    protected static function usesSoftDeletes(): bool
    {
        return in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(static::class),
            true,
        );
    }
}
