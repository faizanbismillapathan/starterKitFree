<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ThemeMode;
use App\Enums\UserStatus;
use App\Support\MediaUrl;
use App\Traits\HasAuditColumns;
use App\Traits\HasMedia;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Application user.
 *
 * Models hold relationships, scopes, accessors and casts only; business rules
 * live in the service layer (02_Project_Rules.md §11).
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasAuditColumns;
    use HasFactory;
    use HasMedia;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'status',
        'avatar_path',
        'locale',
        'timezone',
        'theme',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'theme' => ThemeMode::class,
            'login_count' => 'integer',
        ];
    }

    // ---------------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------------

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class)->latest('created_at');
    }

    // ---------------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Initials rendered by the avatar component when no image is present.
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);

        return mb_strtoupper($first.$last);
    }

    /**
     * Avatar address resolved from the media library.
     *
     * Falls back to the denormalised `avatar_path` column when the media
     * relation has not been eager loaded, keeping listing screens free of
     * N+1 queries (38_Performance_Guide.md §7).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->relationLoaded('media')) {
            return $this->firstMedia((string) config('media.avatar.collection'))?->url();
        }

        if (blank($this->avatar_path)) {
            return null;
        }

        $disk = (string) config('media.disk');

        return Storage::disk($disk)->exists($this->avatar_path)
            ? MediaUrl::resolve($disk, $this->avatar_path)
            : null;
    }

    // ---------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('first_name', 'like', $term)
                ->orWhere('last_name', 'like', $term)
                ->orWhere('email', 'like', $term);
        });
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Whether this account is permitted to establish a session.
     */
    public function canAuthenticate(): bool
    {
        return $this->status->canAuthenticate();
    }

    public function preferredTheme(): ThemeMode
    {
        return $this->theme ?? ThemeMode::from(config('theme.default_mode'));
    }
}
