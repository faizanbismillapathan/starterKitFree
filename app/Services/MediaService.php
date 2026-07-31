<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MediaRepositoryInterface;
use App\Exceptions\MediaUploadException;
use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Centralised upload, optimisation and deletion pipeline.
 *
 * No module may implement its own upload mechanism (16_Media_System.md §1).
 * Filenames are always generated, never taken from user input (§10).
 */
final readonly class MediaService
{
    public function __construct(private MediaRepositoryInterface $media) {}

    /**
     * Stores an uploaded file and attaches it to the owning model.
     *
     * @param  array<string, mixed>  $metadata
     *
     * @throws MediaUploadException
     */
    public function store(
        Model $owner,
        UploadedFile $file,
        string $collection = 'default',
        array $metadata = [],
    ): Media {
        $this->assertSupported($file);

        $disk = (string) config('media.disk');
        $directory = trim((string) config('media.directory'), '/').'/'.$collection;
        $filename = $this->generateFilename($file);

        $stored = $file->storeAs($directory, $filename, ['disk' => $disk]);

        if ($stored === false) {
            throw MediaUploadException::storageFailed($file->getClientOriginalName());
        }

        $dimensions = $this->dimensions($disk, $directory.'/'.$filename, $file);

        return DB::transaction(function () use (
            $owner, $file, $collection, $disk, $directory, $filename, $dimensions, $metadata
        ): Media {
            $media = $this->media->create([
                'mediable_type' => $owner->getMorphClass(),
                'mediable_id' => $owner->getKey(),
                'collection' => $collection,
                'disk' => $disk,
                'directory' => $directory,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'extension' => Str::lower($file->getClientOriginalExtension()),
                'mime_type' => (string) $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'hash' => $this->hash($disk, $directory.'/'.$filename),
                'metadata' => $metadata,
            ]);

            if ($media->isImage()) {
                $media->forceFill([
                    'conversions' => $this->generateConversions($media),
                ])->save();
            }

            return $media->refresh();
        });
    }

    /**
     * Replaces every file in a single-file collection such as an avatar.
     *
     * @throws MediaUploadException
     */
    public function replaceCollection(
        Model $owner,
        UploadedFile $file,
        string $collection,
    ): Media {
        $this->media->deleteCollection($owner, $collection);

        return $this->store($owner, $file, $collection);
    }

    /**
     * Removes a media record together with its stored files.
     */
    public function delete(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        foreach ($this->allPaths($media) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $this->media->delete($media);
    }

    public function deleteCollection(Model $owner, string $collection): void
    {
        $existing = $this->media->findInCollection($owner, $collection);

        if ($existing !== null) {
            $this->delete($existing);
        }
    }

    /**
     * Validates the file against the configured allow lists.
     *
     * @throws MediaUploadException
     */
    private function assertSupported(UploadedFile $file): void
    {
        $mimeType = (string) $file->getMimeType();

        if (! in_array($mimeType, config('media.allowed_mime_types', []), true)) {
            throw MediaUploadException::unsupportedType($mimeType);
        }
    }

    /**
     * Produces a collision-free filename that never echoes user input.
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension() ?: 'bin');

        return Str::uuid()->toString().'.'.$extension;
    }

    /**
     * @return array{width: int|null, height: int|null}
     */
    private function dimensions(string $disk, string $path, UploadedFile $file): array
    {
        if (! in_array((string) $file->getMimeType(), config('media.image_mime_types', []), true)) {
            return ['width' => null, 'height' => null];
        }

        try {
            $contents = Storage::disk($disk)->get($path);

            if ($contents === null) {
                return ['width' => null, 'height' => null];
            }

            $size = @getimagesizefromstring($contents);

            return $size === false
                ? ['width' => null, 'height' => null]
                : ['width' => $size[0], 'height' => $size[1]];
        } catch (Throwable $exception) {
            Log::warning('Unable to read image dimensions.', [
                'path' => $path,
                'exception' => $exception->getMessage(),
            ]);

            return ['width' => null, 'height' => null];
        }
    }

    private function hash(string $disk, string $path): ?string
    {
        try {
            $contents = Storage::disk($disk)->get($path);

            return $contents === null ? null : hash('sha256', $contents);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Generates the documented thumbnail sizes while preserving the original.
     *
     * @return array<string, string>
     */
    private function generateConversions(Media $media): array
    {
        $disk = Storage::disk($media->disk);
        $conversions = [];

        try {
            $manager = new ImageManager(new Driver);
            $source = $disk->get($media->path());

            if ($source === null) {
                return [];
            }

            foreach ((array) config('media.conversions') as $name => $size) {
                $image = $manager->read($source)->scaleDown(
                    width: (int) $size['width'],
                    height: (int) $size['height'],
                );

                $path = $media->directory.'/'.$name.'-'.$media->filename;

                $disk->put($path, (string) $image->encodeByExtension(
                    $media->extension === 'gif' ? 'png' : $media->extension,
                    quality: (int) config('media.optimize.quality'),
                ));

                $conversions[$name] = $path;
            }
        } catch (Throwable $exception) {
            Log::warning('Image conversion failed.', [
                'media_id' => $media->getKey(),
                'exception' => $exception->getMessage(),
            ]);
        }

        return $conversions;
    }

    /**
     * @return array<int, string>
     */
    private function allPaths(Media $media): array
    {
        return array_values(array_merge(
            [$media->path()],
            array_values($media->conversions ?? []),
        ));
    }
}
