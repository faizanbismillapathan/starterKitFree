<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoginHistory
 */
final class LoginHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'ip_address' => $this->ip_address,
            'browser' => $this->browser,
            'platform' => $this->platform,
            'device' => $this->device,
            'logged_in_at' => $this->logged_in_at?->toIso8601String(),
            'logged_out_at' => $this->logged_out_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
