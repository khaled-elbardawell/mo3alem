<?php

namespace App\Http\Requests;

use App\Models\ShortLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShortLinkQrDesignRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'design' => ['required', 'array'],
            'design.style' => ['required', Rule::in(['classic', 'dots', 'rounded'])],
            'design.foreground_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.eye_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'version' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'design.style.in' => 'اختر نمط QR صالحًا.',
            'design.foreground_color.regex' => 'لون الرمز غير صالح.',
            'design.eye_color.regex' => 'لون الزوايا غير صالح.',
            'design.background_color.regex' => 'لون الخلفية غير صالح.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $design = $this->input('design', []);

        if (! is_array($design)) {
            return;
        }

        $this->merge([
            'design' => collect($design)
                ->map(fn (mixed $value): mixed => is_string($value) ? strtolower(trim($value)) : $value)
                ->all(),
        ]);
    }
}
