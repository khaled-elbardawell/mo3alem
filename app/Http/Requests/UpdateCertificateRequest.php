<?php

namespace App\Http\Requests;

use App\Models\Certificate;

class UpdateCertificateRequest extends StoreCertificateRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $certificate = $this->route('certificate');

        return $certificate instanceof Certificate
            && ($this->user()?->can('update', $certificate) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'version' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function hasPersistedCustomBackground(): bool
    {
        $certificate = $this->route('certificate');

        return $certificate instanceof Certificate && filled($certificate->background_path);
    }
}
