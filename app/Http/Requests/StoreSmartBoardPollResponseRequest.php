<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSmartBoardPollResponseRequest extends FormRequest
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
            'poll_key' => ['required', 'string', 'max:64'],
            'option_key' => ['required', 'string', 'max:64'],
            'participant_token' => ['required', 'string', 'min:20', 'max:128'],
        ];
    }
}
