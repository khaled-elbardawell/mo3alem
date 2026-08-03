<?php

namespace App\Http\Requests;

use App\Models\Certificate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCertificateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Certificate::class) ?? false;
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
            'template_key' => ['required', Rule::in($this->templateKeys())],
            'design' => ['required', 'array'],
            'design.width' => ['required', 'integer', 'min:600', 'max:2400'],
            'design.height' => ['required', 'integer', 'min:400', 'max:1800'],
            'design.elements' => ['present', 'array', 'max:50'],
            'design.elements.*.id' => ['required', 'string', 'max:80', 'distinct'],
            'design.elements.*.type' => ['required', Rule::in(['text'])],
            'design.elements.*.text' => ['required', 'string', 'max:1000'],
            'design.elements.*.x' => ['required', 'numeric', 'min:0', 'max:2400'],
            'design.elements.*.y' => ['required', 'numeric', 'min:0', 'max:1800'],
            'design.elements.*.width' => ['required', 'numeric', 'min:40', 'max:2400'],
            'design.elements.*.height' => ['required', 'numeric', 'min:20', 'max:1800'],
            'design.elements.*.font_size' => ['required', 'numeric', 'min:8', 'max:240'],
            'design.elements.*.font_family' => ['required', Rule::in(['Tajawal', 'Cairo', 'Amiri', 'Noto Kufi Arabic'])],
            'design.elements.*.font_weight' => ['required', Rule::in([400, 500, 700, 800, 900])],
            'design.elements.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.elements.*.text_align' => ['required', Rule::in(['right', 'center', 'left'])],
            'design.elements.*.direction' => ['required', Rule::in(['rtl', 'ltr'])],
            'design.elements.*.rotation' => ['required', 'numeric', 'min:-180', 'max:180'],
            'design.elements.*.opacity' => ['required', 'numeric', 'min:0.1', 'max:1'],
            'design.elements.*.locked' => ['required', 'boolean'],
            'background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:min_width=600,min_height=400,max_width=4000,max_height=4000'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->string('template_key')->toString() !== 'custom' && $this->hasFile('background')) {
                    $validator->errors()->add('background', 'لا يمكن إرفاق خلفية مع قالب جاهز.');
                }

                if ($this->string('template_key')->toString() === 'custom'
                    && ! $this->hasFile('background')
                    && ! $this->hasPersistedCustomBackground()) {
                    $validator->errors()->add('background', 'ارفع صورة القالب المخصص قبل الحفظ.');
                }
            },
        ];
    }

    protected function hasPersistedCustomBackground(): bool
    {
        return false;
    }

    /** @return list<string> */
    private function templateKeys(): array
    {
        return ['b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'b11', 'custom'];
    }
}
