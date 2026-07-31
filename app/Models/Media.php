<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\MediaUrl;
use App\Traits\HasAuditColumns;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * A single asset stored in the centralised media library.
 *
 * Storage paths are never exposed directly (02_Project_Rules.md §17); consumers
 * resolve public addresses through {@see self::url()}.
 */
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasAuditColumns;

    use HasFactory;
    use SoftDeletes;

    protected $table = 'media';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'directory',
        'filename',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'width',
        'height',
        'hash',
        'conversions',
        'metadata',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversions' => 'array',
            'metadata' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relative path of the stored original file.
     */
    public function path(): string
    {
        return trim($this->directory, '/').'/'.$this->filename;
    }

    /**
     * Publicly resolvable address for the original file.
     */
    public function url(): ?string
    {
        return Storage::disk($this->disk)->exists($this->path())
            ? MediaUrl::resolve($this->disk, $this->path())
            : null;
    }

    /**
     * Address of a generated conversion, falling back to the original.
     */
    public function conversionUrl(string $conversion): ?string
    {
        $path = $this->conversions[$conversion] ?? null;

        if ($path === null) {
            return $this->url();
        }

        return Storage::disk($this->disk)->exists($path)
            ? MediaUrl::resolve($this->disk, $path)
            : $this->url();
    }

    public function isImage(): bool
    {
        return in_array($this->mime_type, config('media.image_mime_types', []), true);
    }

    public function humanReadableSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }
}
