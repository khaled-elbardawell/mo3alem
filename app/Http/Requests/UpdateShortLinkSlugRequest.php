<?php

namespace App\Http\Requests;

use App\Models\ShortLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShortLinkSlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shortLink = $this->route('shortLink');

        return $shortLink instanceof ShortLink
            && ($this->user()?->can('update', $shortLink) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $shortLink = $this->route('shortLink');

        return [
            'slug' => [
                'required',
                'string',
                'min:4',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(ShortLink::class, 'slug')->ignore($shortLink),
            ],
            'version' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.required' => 'اكتب المقطع المخصص الذي تريده.',
            'slug.min' => 'المقطع المخصص يجب ألا يقل عن 4 أحرف.',
            'slug.max' => 'المقطع المخصص يجب ألا يزيد عن 50 حرفًا.',
            'slug.regex' => 'استخدم أحرفًا إنجليزية صغيرة وأرقامًا، ويمكن الفصل بشرطة.',
            'slug.unique' => 'هذا المقطع مستخدم أو محجوز سابقًا. جرّب مقطعًا آخر.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->string('slug')->trim()->lower()->toString(),
        ]);
    }
}
