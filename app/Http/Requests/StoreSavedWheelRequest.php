<?php

namespace App\Http\Requests;

use App\Models\SavedWheel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSavedWheelRequest extends FormRequest
{
    /**
     * Remove blank imported rows before validating individual names.
     */
    protected function prepareForValidation(): void
    {
        $names = $this->input('names');

        if (! is_array($names)) {
            return;
        }

        $this->merge([
            'names' => array_values(array_filter(
                array_map(
                    static fn (mixed $name): mixed => is_string($name) ? trim($name) : $name,
                    $names,
                ),
                static fn (mixed $name): bool => $name !== null && $name !== '',
            )),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', SavedWheel::class) ?? false;
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
            'names' => ['present', 'array', 'max:'.config('resource_limits.names_per_saved_wheel', 2000)],
            'names.*' => ['required', 'string', 'max:120'],
        ];
    }
}
