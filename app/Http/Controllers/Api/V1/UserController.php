<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ProvisionApiUser;
use App\Http\Controllers\Concerns\InteractsWithApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Models\ApiClient;
use App\Services\AdminAuditService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use InteractsWithApiResponses;

    public function __invoke(
        StoreUserRequest $request,
        ProvisionApiUser $provisionUser,
        AdminAuditService $audit,
    ): JsonResponse {
        /** @var ApiClient $apiClient */
        $apiClient = $request->user();
        $result = $provisionUser->execute($apiClient, $request->validated());

        $audit->recordApiClient(
            $request,
            $apiClient,
            $result->created ? 'api.user.created' : 'api.user.reused',
            $result->user,
            [
                'external_id' => $result->link->external_id,
                'temporary_password_issued' => $result->temporaryPassword !== null,
            ],
        );

        $data = [
            'id' => $result->user->getKey(),
            'external_id' => $result->link->external_id,
            'name' => $result->user->name,
            'email' => $result->user->email,
            'created' => $result->created,
        ];

        if ($result->temporaryPassword !== null) {
            $data['temporary_password'] = $result->temporaryPassword;
        }

        return $this->apiSuccess(
            data: $data,
            message: $result->created ? 'تم إنشاء المستخدم بنجاح.' : 'تم استرجاع المستخدم بنجاح.',
            status: $result->created ? Response::HTTP_CREATED : Response::HTTP_OK,
        );
    }
}
