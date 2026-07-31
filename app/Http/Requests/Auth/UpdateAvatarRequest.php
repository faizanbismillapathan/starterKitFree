<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an avatar upload against the configured media policy.
 */
final class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var array<int, string> $mimeTypes */
        $mimeTypes = (array) config('media.avatar.mime_types');

        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimetypes:'.implode(',', $mimeTypes),
                'max:'.(int) config('media.avatar.max_size'),
                'dimensions:min_width=64,min_height=64',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.mimetypes' => __('profile.errors.avatar_type'),
            'avatar.dimensions' => __('profile.errors.avatar_dimensions'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['avatar' => __('profile.fields.avatar')];
    }
}
