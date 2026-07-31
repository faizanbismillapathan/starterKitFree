<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
final class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size' => $this->size,
            'human_size' => $this->humanReadableSize(),
            'width' => $this->width,
            'height' => $this->height,
            'url' => $this->url(),
            'thumbnail_url' => $this->when(
                $this->isImage(),
                fn (): ?string => $this->conversionUrl('small'),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
