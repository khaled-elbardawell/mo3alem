<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {email? : البريد الإلكتروني} {--name= : اسم الأدمن}')]
#[Description('إنشاء حساب مدير أو ترقية حساب موجود بأمان')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: text('البريد الإلكتروني', required: true));
        $name = (string) ($this->option('name') ?: text('الاسم', required: true));
        $plainPassword = password('كلمة السر (12 حرفًا على الأقل)', required: true);

        $validator = Validator::make(compact('email', 'name') + ['password' => $plainPassword], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $plainPassword,
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
            'deleted_at' => null,
        ])->save();

        $this->info("تم تجهيز حساب المدير: {$user->email}");

        return self::SUCCESS;
    }
}
