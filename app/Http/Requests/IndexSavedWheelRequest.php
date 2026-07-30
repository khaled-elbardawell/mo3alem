<?php

namespace App\Http\Requests;

use App\Models\SavedWheel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexSavedWheelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', SavedWheel::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'cursor' => ['nullable', 'string'],
        ];
    }
}
