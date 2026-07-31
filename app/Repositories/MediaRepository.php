<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\MediaRepositoryInterface;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent implementation of the media data access contract.
 */
final class MediaRepository implements MediaRepositoryInterface
{
    public function find(int $id): ?Media
    {
        return Media::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Media
    {
        return Media::query()->create($attributes);
    }

    public function delete(Media $media): bool
    {
        return (bool) $media->delete();
    }

    public function deleteCollection(Model $model, string $collection): void
    {
        Media::query()
            ->where('mediable_type', $model->getMorphClass())
            ->where('mediable_id', $model->getKey())
            ->where('collection', $collection)
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    public function findInCollection(Model $model, string $collection): ?Media
    {
        return Media::query()
            ->where('mediable_type', $model->getMorphClass())
            ->where('mediable_id', $model->getKey())
            ->where('collection', $collection)
            ->latest('id')
            ->first();
    }

    public function totalSize(): int
    {
        return (int) Media::query()->sum('size');
    }

    public function count(): int
    {
        return Media::query()->count();
    }
}
