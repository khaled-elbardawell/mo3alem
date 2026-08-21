<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeoSettingUpdateRequest extends FormRequest
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
            'site_name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'allow_indexing' => ['required', 'boolean'],
            'allow_following' => ['required', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:180'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
            'og_image_alt' => ['nullable', 'string', 'max:180'],
            'remove_og_image' => ['required', 'boolean'],
            'twitter_card' => ['required', 'in:summary,summary_large_image'],
            'include_in_sitemap' => ['required', 'boolean'],
            'sitemap_change_frequency' => ['required', Rule::in(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])],
            'sitemap_priority' => ['required', 'numeric', 'between:0,1', 'decimal:1'],
        ];
    }
}
