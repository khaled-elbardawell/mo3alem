<?php

namespace App\Http\Requests;

use App\Models\SmartBoard;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSmartBoardAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $smartBoard = $this->route('smartBoard');

        return $smartBoard instanceof SmartBoard && ($this->user()?->can('update', $smartBoard) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required', 'file', 'image', 'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp', 'max:5120',
            ],
        ];
    }
}
