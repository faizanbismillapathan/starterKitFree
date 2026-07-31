<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
final class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'webp']);
        $filename = Str::uuid()->toString().'.'.$extension;

        return [
            'mediable_type' => (new User)->getMorphClass(),
            'mediable_id' => User::factory(),
            'collection' => 'default',
            'disk' => config('media.disk'),
            'directory' => config('media.directory').'/default',
            'filename' => $filename,
            'original_name' => fake()->word().'.'.$extension,
            'extension' => $extension,
            'mime_type' => 'image/'.($extension === 'jpg' ? 'jpeg' : $extension),
            'size' => fake()->numberBetween(20_000, 900_000),
            'width' => fake()->numberBetween(200, 2000),
            'height' => fake()->numberBetween(200, 2000),
            'hash' => hash('sha256', $filename),
            'conversions' => [],
            'metadata' => [],
            'sort_order' => 0,
        ];
    }

    public function avatar(): static
    {
        return $this->state(fn (): array => [
            'collection' => config('media.avatar.collection'),
            'directory' => config('media.directory').'/'.config('media.avatar.collection'),
        ]);
    }
}
