<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQrTemplateRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:120'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:8192', 'dimensions:min_width=500,min_height=500,max_width=4000,max_height=4000'],
            'width' => ['required', 'integer', 'between:500,4000'],
            'height' => ['required', 'integer', 'between:500,4000'],
            'qr_x' => ['required', 'integer', 'between:0,4000'],
            'qr_y' => ['required', 'integer', 'between:0,4000'],
            'qr_size' => ['required', 'integer', 'between:100,4000'],
            'sort_order' => ['required', 'integer', 'between:0,10000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $width = $this->integer('width');
                $height = $this->integer('height');
                $qrX = $this->integer('qr_x');
                $qrY = $this->integer('qr_y');
                $qrSize = $this->integer('qr_size');

                if ($qrX + $qrSize > $width) {
                    $validator->errors()->add('qr_x', 'يجب أن يقع الرمز كاملًا داخل عرض القالب.');
                }

                if ($qrY + $qrSize > $height) {
                    $validator->errors()->add('qr_y', 'يجب أن يقع الرمز كاملًا داخل ارتفاع القالب.');
                }
            },
        ];
    }
}
