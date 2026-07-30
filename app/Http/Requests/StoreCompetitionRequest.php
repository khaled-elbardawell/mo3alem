<?php

namespace App\Http\Requests;

use App\Models\Competition;
use App\Models\SavedWheel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCompetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Competition::class) ?? false;
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
            'saved_wheel_id' => [
                'nullable',
                'required_without:new_list_title',
                'integer',
                Rule::exists((new SavedWheel)->getTable(), 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->where('user_id', $this->user()->id)
                        ->whereNull('deleted_at'),
                ),
            ],
            'new_list_title' => [
                'nullable',
                'required_without:saved_wheel_id',
                'string',
                'max:120',
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->filled('saved_wheel_id') && $this->filled('new_list_title')) {
                    $validator->errors()->add(
                        'saved_wheel_id',
                        'اختر قائمة محفوظة أو أنشئ قائمة جديدة، وليس الخيارين معًا.',
                    );
                }
            },
        ];
    }
}
