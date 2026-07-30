<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('competition')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'version' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'names' => ['sometimes', 'array', 'max:2000'],
            'names.*' => ['required', 'string', 'max:120'],
            'results' => ['sometimes', 'array', 'max:10000'],
            'results.*' => ['required', 'array'],
            'results.*.round' => ['required', 'integer', 'min:1'],
            'results.*.name' => ['required', 'string', 'max:120'],
            'results.*.date' => ['required', 'date'],
            'results.*.position' => ['nullable', 'integer', 'min:1', 'max:2000'],
        ];
    }
}
