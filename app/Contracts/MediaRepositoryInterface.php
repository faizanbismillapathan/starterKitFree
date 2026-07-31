<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;

/**
 * Data access contract for the centralised media library.
 */
interface MediaRepositoryInterface
{
    public function find(int $id): ?Media;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Media;

    public function delete(Media $media): bool;

    public function deleteCollection(Model $model, string $collection): void;

    public function findInCollection(Model $model, string $collection): ?Media;

    public function totalSize(): int;

    public function count(): int;
}
