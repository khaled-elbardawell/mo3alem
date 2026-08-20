<?php

namespace App\Actions;

use App\Data\ApiUserProvisionResult;
use App\Models\ApiClient;
use App\Models\ExternalUserLink;
use App\Models\User;
use App\Services\MetricService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ProvisionApiUser
{
    public function __construct(private readonly MetricService $metrics) {}

    /** @param array{name: string, email: string, external_id: string} $attributes */
    public function execute(ApiClient $apiClient, array $attributes): ApiUserProvisionResult
    {
        $lockKey = hash_hmac(
            'sha256',
            $apiClient->getKey().':'.$attributes['external_id'],
            (string) config('app.key'),
        );

        try {
            $result = Cache::lock("api-user-provision:{$lockKey}", 10)->block(
                5,
                fn (): ApiUserProvisionResult => DB::transaction(
                    fn (): ApiUserProvisionResult => $this->provision($apiClient, $attributes),
                    attempts: 3,
                ),
            );
        } catch (LockTimeoutException) {
            throw new ServiceUnavailableHttpException(1, 'تعذر معالجة الطلب المتزامن. حاول مرة أخرى.');
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw new ConflictHttpException('تعذر إنشاء الحساب بهذه البيانات.', $exception);
            }

            throw $exception;
        }

        if ($result->created) {
            $this->metrics->increment('registrations');
        }

        return $result;
    }

    /** @param array{name: string, email: string, external_id: string} $attributes */
    private function provision(ApiClient $apiClient, array $attributes): ApiUserProvisionResult
    {
        $existingLink = ExternalUserLink::query()
            ->whereBelongsTo($apiClient)
            ->where('external_id', $attributes['external_id'])
            ->first();

        if ($existingLink) {
            $existingUser = $existingLink->user;

            if (! $existingUser || $existingUser->trashed() || $existingUser->email !== $attributes['email']) {
                throw new ConflictHttpException('معرّف المستخدم الخارجي مستخدم مسبقًا ببيانات مختلفة.');
            }

            return new ApiUserProvisionResult($existingUser, $existingLink, false, null);
        }

        if (User::withTrashed()->where('email', $attributes['email'])->exists()) {
            throw new ConflictHttpException('تعذر إنشاء الحساب بهذه البيانات.');
        }

        $temporaryPassword = Str::password(length: 20, symbols: false);

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => $temporaryPassword,
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
            'must_change_password' => true,
        ])->save();

        $link = $apiClient->externalUserLinks()->create([
            'user_id' => $user->getKey(),
            'external_id' => $attributes['external_id'],
        ]);

        return new ApiUserProvisionResult($user, $link, true, $temporaryPassword);
    }
}
