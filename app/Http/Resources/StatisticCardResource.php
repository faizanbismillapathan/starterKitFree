<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\StatisticCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StatisticCard
 */
final class StatisticCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'icon' => $this->icon,
            'trend' => $this->trend,
            'formatted_trend' => $this->formattedTrend(),
            'caption' => $this->caption,
        ];
    }
}
