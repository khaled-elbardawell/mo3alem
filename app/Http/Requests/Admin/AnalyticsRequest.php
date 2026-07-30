<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsRequest extends FormRequest
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
            'range' => ['nullable', Rule::in(['7', '30', 'year', 'custom'])],
            'from' => ['nullable', 'required_if:range,custom', 'date'],
            'to' => ['nullable', 'required_if:range,custom', 'date', 'after_or_equal:from'],
        ];
    }
}
