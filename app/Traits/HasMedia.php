<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Grants a model access to the centralised media library.
 *
 * Modules must never implement their own upload mechanism
 * (16_Media_System.md §1).
 */
trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Convenience relation for single-file collections such as avatars.
     */
    public function singleMedia(string $collection): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('collection', $collection)
            ->latestOfMany();
    }

    /**
     * @return Collection<int, Media>
     */
    public function mediaFromCollection(string $collection): Collection
    {
        /** @var Collection<int, Media> $items */
        $items = $this->media->where('collection', $collection)->values();

        return $items;
    }

    public function firstMedia(string $collection): ?Media
    {
        return $this->media->firstWhere('collection', $collection);
    }
}
