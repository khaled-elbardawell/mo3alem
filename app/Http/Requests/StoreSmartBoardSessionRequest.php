<?php

namespace App\Http\Requests;

use App\Models\SmartBoard;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSmartBoardSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $boardId = $this->integer('smart_board_id');

        if (! $boardId) {
            return true;
        }

        $smartBoard = SmartBoard::query()->find($boardId);

        return $smartBoard && ($this->user()?->can('view', $smartBoard) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'smart_board_id' => ['nullable', 'integer', Rule::exists(SmartBoard::class, 'id')],
            'poll_key' => ['nullable', 'string', 'max:64'],
            'poll' => ['nullable', 'array'],
            'poll.question' => ['required_with:poll', 'string', 'max:180'],
            'poll.options' => ['required_with:poll', 'array', 'min:2', 'max:8'],
            'poll.options.*.key' => ['required', 'string', 'max:64', 'distinct'],
            'poll.options.*.label' => ['required', 'string', 'max:80'],
        ];
    }
}
