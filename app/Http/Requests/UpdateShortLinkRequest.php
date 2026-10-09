<?php

namespace App\Http\Requests;

use App\Models\ShortLink;

class UpdateShortLinkRequest extends StoreShortLinkRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $shortLink = $this->route('shortLink');

        return $shortLink instanceof ShortLink
            && ($this->user()?->can('update', $shortLink) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->editableRules(),
            'version' => ['required', 'integer', 'min:1'],
        ];
    }
}
