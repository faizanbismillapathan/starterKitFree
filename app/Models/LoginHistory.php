<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoginStatus;
use Database\Factories\LoginHistoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable record of an authentication attempt.
 *
 * See 17_Authentication_Module.md §23.
 */
class LoginHistory extends Model
{
    /** @use HasFactory<LoginHistoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'status',
        'ip_address',
        'browser',
        'platform',
        'device',
        'user_agent',
        'session_id',
        'logged_in_at',
        'logged_out_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LoginStatus::class,
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', LoginStatus::Successful);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', LoginStatus::Failed);
    }
}
