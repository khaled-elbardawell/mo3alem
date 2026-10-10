<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSmartBoardSessionRequest extends FormRequest
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
            'poll_key' => ['nullable', 'string', 'max:64'],
            'poll' => ['nullable', 'array'],
            'poll.question' => ['required_with:poll', 'string', 'max:180'],
            'poll.options' => ['required_with:poll', 'array', 'min:2', 'max:8'],
            'poll.options.*.key' => ['required', 'string', 'max:64', 'distinct'],
            'poll.options.*.label' => ['required', 'string', 'max:80'],
            'status' => ['sometimes', 'required', 'in:active,closed'],
        ];
    }
}
