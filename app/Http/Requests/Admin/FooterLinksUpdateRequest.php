<?php

namespace App\Http\Requests\Admin;

use App\FooterLinkPlatform;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FooterLinksUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'footer_links' => ['present', 'array', 'max:12'],
            'footer_links.*.platform' => ['bail', 'required', Rule::enum(FooterLinkPlatform::class)],
            'footer_links.*.label' => ['required', 'string', 'max:80'],
            'footer_links.*.url' => [
                'bail',
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! $this->isSafeFooterUrl($value)) {
                        $fail('يجب أن يكون الرابط عنوانًا آمنًا يبدأ بـ https:// أو http:// أو / أو #.');
                    }
                },
            ],
            'footer_links.*.open_in_new_tab' => ['required', 'boolean'],
            'footer_links.*.is_active' => ['required', 'boolean'],
        ];
    }

    private function isSafeFooterUrl(string $url): bool
    {
        if (Str::startsWith($url, ['/', '#'])) {
            return ! Str::startsWith($url, '//');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
