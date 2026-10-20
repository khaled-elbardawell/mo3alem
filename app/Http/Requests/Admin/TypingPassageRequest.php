<?php

namespace App\Http\Requests\Admin;

use App\Models\TypingPassage;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TypingPassageRequest extends FormRequest
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
            'language' => ['required', Rule::enum(TypingPassageLanguage::class)],
            'content' => ['required', 'string', 'min:20', 'max:800'],
            'auto_calculate_time' => ['required', 'boolean'],
            'expected_seconds' => [
                Rule::requiredIf(! $this->boolean('auto_calculate_time')),
                'nullable',
                'integer',
                'between:10,600',
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => app(TypingPassageCalculator::class)->normalize($this->string('content')->toString()),
            'auto_calculate_time' => $this->boolean('auto_calculate_time'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('content')) {
                    return;
                }

                $hash = hash('sha256', $this->string('content')->toString());
                $passage = $this->route('typingPassage');
                $duplicateExists = TypingPassage::withTrashed()
                    ->where('content_hash', $hash)
                    ->when($passage instanceof TypingPassage, fn ($query) => $query->whereKeyNot($passage))
                    ->exists();

                if ($duplicateExists) {
                    $validator->errors()->add('content', 'هذه الفقرة موجودة مسبقا.');
                }
            },
        ];
    }
}
