<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavedWheelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('savedWheel')) ?? false;
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
            'names' => ['sometimes', 'array', 'max:'.config('resource_limits.names_per_saved_wheel')],
            'names.*' => ['required', 'string', 'max:120'],
        ];
    }
}
