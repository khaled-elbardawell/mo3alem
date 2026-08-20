<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreApiClientRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'allowed_ips' => ['present', 'array', 'max:20'],
            'allowed_ips.*' => ['required', 'ip', 'distinct'],
            'token_expiration_days' => ['required', 'integer', 'between:1,90'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $allowedIps = $this->input('allowed_ips', []);

        if (! is_array($allowedIps)) {
            $allowedIps = preg_split('/[\s,;]+/', trim((string) $allowedIps), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'allowed_ips' => array_values(array_unique($allowedIps)),
        ]);
    }
}
