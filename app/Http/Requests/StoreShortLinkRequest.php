<?php

namespace App\Http\Requests;

use App\Models\ShortLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreShortLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', ShortLink::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->editableRules(),
            'custom_alias' => [
                'nullable',
                'string',
                'min:4',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(ShortLink::class, 'slug'),
            ],
        ];
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    protected function editableRules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'destination_url' => ['required', 'string', 'url:http,https', 'max:2048'],
            'is_active' => ['required', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->pointsToLocalShortLink($this->string('destination_url')->toString())) {
                    $validator->errors()->add(
                        'destination_url',
                        'لا يمكن اختصار رابط مختصر آخر من نفس الموقع.',
                    );
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'destination_url.required' => 'أدخل الرابط الذي تريد اختصاره.',
            'destination_url.url' => 'أدخل رابطًا صحيحًا يبدأ بـ http:// أو https://.',
            'destination_url.max' => 'الرابط طويل جدًا؛ الحد الأقصى 2048 حرفًا.',
            'custom_alias.min' => 'المقطع المخصص يجب ألا يقل عن 4 أحرف.',
            'custom_alias.max' => 'المقطع المخصص يجب ألا يزيد عن 50 حرفًا.',
            'custom_alias.regex' => 'استخدم أحرفًا إنجليزية صغيرة وأرقامًا، ويمكن الفصل بشرطة.',
            'custom_alias.unique' => 'هذا المقطع مستخدم أو محجوز سابقًا.',
            'expires_at.after' => 'اختر تاريخ انتهاء في المستقبل.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->filled('title') ? $this->string('title')->trim()->toString() : null,
            'destination_url' => $this->string('destination_url')->trim()->toString(),
            'custom_alias' => $this->filled('custom_alias')
                ? $this->string('custom_alias')->trim()->lower()->toString()
                : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    private function pointsToLocalShortLink(string $destinationUrl): bool
    {
        $destinationHost = Str::lower((string) parse_url($destinationUrl, PHP_URL_HOST));
        $applicationHost = Str::lower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $destinationPath = '/'.ltrim((string) parse_url($destinationUrl, PHP_URL_PATH), '/');

        return $destinationHost !== ''
            && $destinationHost === $applicationHost
            && Str::startsWith($destinationPath, '/short/');
    }
}
