<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\MetricService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(private readonly MetricService $metrics) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $throttleKey = 'registration:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        RateLimiter::hit($throttleKey, 3600);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => Str::lower($input['email']),
            'password' => Hash::make($input['password']),
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $this->metrics->increment('registrations');
        $this->metrics->recordActiveUser($user);

        return $user;
    }
}
