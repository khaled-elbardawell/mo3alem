<?php

namespace App\Http\Requests\Admin;

use App\AdCampaignStatus;
use App\AdPlacement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdCampaignStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'target_url' => ['required', 'url:http,https', 'max:2048'],
            'alt_text' => ['required', 'string', 'max:180'],
            'placement' => ['required', Rule::enum(AdPlacement::class)],
            'status' => ['required', Rule::enum(AdCampaignStatus::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'weight' => ['required', 'integer', 'between:1,1000'],
        ];
    }
}
