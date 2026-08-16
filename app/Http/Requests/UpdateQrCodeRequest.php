<?php

namespace App\Http\Requests;

use App\Models\QrCode;
use Illuminate\Validation\Rule;

class UpdateQrCodeRequest extends StoreQrCodeRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $qrCode = $this->route('qrCode');

        return $qrCode instanceof QrCode
            && ($this->user()?->can('update', $qrCode) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $qrCode = $this->route('qrCode');

        return [
            ...parent::rules(),
            'mode' => ['required', Rule::in([$qrCode instanceof QrCode ? $qrCode->mode->value : null])],
            'version' => ['required', 'integer', 'min:1'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $qrCode = $this->route('qrCode');

        $this->mergeIfMissing([
            'mode' => $qrCode instanceof QrCode ? $qrCode->mode->value : 'static',
        ]);
    }
}
