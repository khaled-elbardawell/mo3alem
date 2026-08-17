<?php

namespace App\Http\Requests;

use App\QrCodeMode;
use App\QrContentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RenderQrCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::enum(QrCodeMode::class)],
            'qr_code_id' => ['nullable', 'integer', 'min:1'],
            'content_type' => ['required', Rule::enum(QrContentType::class)],
            'payload' => ['required', 'array'],
            'payload.url' => ['required_if:content_type,url', 'nullable', 'url:http,https', 'max:2048'],
            'payload.text' => ['required_if:content_type,text', 'nullable', 'string', 'max:2000'],
            'payload.ssid' => ['required_if:content_type,wifi', 'nullable', 'string', 'max:100'],
            'payload.password' => ['nullable', 'string', 'max:100'],
            'payload.encryption' => ['required_if:content_type,wifi', 'nullable', Rule::in(['WPA', 'WEP', 'nopass'])],
            'payload.hidden' => ['nullable', 'boolean'],
            'design' => ['required', 'array'],
            'design.style' => ['required', Rule::in(['classic', 'dots', 'rounded'])],
            'design.foreground_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.eye_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design.frame' => ['required', Rule::in(['none', 'template-1', 'template-2', 'template-3', 'template-4', 'template-5', 'template-6', 'template-7', 'template-8', 'template-9', 'template-10', 'template-11'])],
            'design.center_type' => ['required', Rule::in(['none', 'text', 'image'])],
            'design.center_text' => ['nullable', 'required_if:design.center_type,text', 'string', 'max:15'],
            'is_active' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->string('mode')->toString() === QrCodeMode::Dynamic->value
                    && $this->string('content_type')->toString() !== QrContentType::Url->value) {
                    $validator->errors()->add('content_type', 'الرمز الديناميكي يدعم الروابط فقط.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'mode' => QrCodeMode::Static->value,
        ]);
    }
}
