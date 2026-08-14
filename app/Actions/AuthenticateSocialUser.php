<?php

namespace App\Actions;

use App\Exceptions\SocialAuthenticationException;
use App\Models\User;
use App\SocialProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticateSocialUser
{
    public function handle(SocialProvider $provider, SocialiteUser $socialiteUser): User
    {
        $providerUserId = trim((string) $socialiteUser->getId());

        if ($providerUserId === '' || Str::length($providerUserId) > 255) {
            throw SocialAuthenticationException::missingIdentity();
        }

        return DB::transaction(function () use ($provider, $providerUserId, $socialiteUser): User {
            $providerColumn = $provider->userIdColumn();
            $existingUser = User::withTrashed()
                ->where($providerColumn, $providerUserId)
                ->lockForUpdate()
                ->first();

            if ($existingUser) {
                if ($existingUser->trashed() || ! $existingUser->isActive()) {
                    throw SocialAuthenticationException::unavailableAccount();
                }

                return $existingUser;
            }

            $email = Str::lower(trim((string) $socialiteUser->getEmail()));

            if ($email === '' || Str::length($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw SocialAuthenticationException::missingIdentity();
            }

            $registeredUser = User::withTrashed()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($registeredUser) {
                if ($registeredUser->trashed() || ! $registeredUser->isActive()) {
                    throw SocialAuthenticationException::unavailableAccount();
                }

                $linkedProviderUserId = $registeredUser->getAttribute($providerColumn);

                if ($linkedProviderUserId !== null && $linkedProviderUserId !== $providerUserId) {
                    throw SocialAuthenticationException::providerAlreadyLinked();
                }

                $registeredUser->forceFill([
                    $providerColumn => $providerUserId,
                    'email_verified_at' => $registeredUser->email_verified_at ?? now(),
                ])->save();

                return $registeredUser;
            }

            $name = Str::of((string) $socialiteUser->getName())->trim()->limit(255, '')->toString();

            if ($name === '') {
                $name = Str::before($email, '@');
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Str::random(64),
            ]);

            $user->forceFill([
                $providerColumn => $providerUserId,
                'email_verified_at' => now(),
            ])->save();

            return $user;
        }, 3);
    }
}
