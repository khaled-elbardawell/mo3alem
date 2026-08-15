<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SavedWheelUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('names_text')) {
            $names = collect(preg_split('/\R/u', $this->string('names_text')->toString()) ?: [])
                ->map(fn (string $name): string => trim($name))
                ->filter()
                ->values()
                ->all();

            $this->merge(['names' => $names]);
        }
    }

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
            'title' => ['required', 'string', 'max:120'],
            'names_text' => ['nullable', 'string'],
            'names' => ['present', 'array', 'max:'.config('resource_limits.names_per_saved_wheel')],
            'names.*' => ['required', 'string', 'max:120'],
        ];
    }
}
