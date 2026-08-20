<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApiClientRequest;
use App\Http\Requests\Admin\UpdateApiClientRequest;
use App\Models\ApiClient;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Sanctum\NewAccessToken;

class ApiClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $apiClients = ApiClient::query()
            ->withCount(['tokens', 'externalUserLinks'])
            ->with('latestToken')
            ->latest()
            ->paginate(20);

        return view('admin.api-clients.index', compact('apiClients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.api-clients.form', [
            'apiClient' => new ApiClient([
                'allowed_ips' => [],
                'token_expiration_days' => 90,
                'is_active' => true,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreApiClientRequest $request,
        AdminAuditService $audit,
    ): RedirectResponse {
        [$apiClient, $accessToken] = DB::transaction(function () use ($request): array {
            $apiClient = ApiClient::query()->create($request->validated());

            return [$apiClient, $apiClient->is_active ? $this->issueToken($apiClient) : null];
        });

        $audit->record($request, 'api-client.created', $apiClient, null, $apiClient->toArray());

        $redirect = redirect()->route('admin.api-clients.index')->with(
            'status',
            $accessToken ? 'تم إنشاء عميل API وتوليد التوكن.' : 'تم إنشاء عميل API بحالة معطّلة دون توكن.',
        );

        return $accessToken
            ? $redirect->with('plain_api_token', $accessToken->plainTextToken)->with('api_client_name', $apiClient->name)
            : $redirect;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiClient $apiClient): View
    {
        return view('admin.api-clients.form', compact('apiClient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateApiClientRequest $request,
        ApiClient $apiClient,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $apiClient->toArray();
        $apiClient->update($request->validated());

        if (! $apiClient->is_active) {
            $apiClient->tokens()->delete();
        }

        $audit->record($request, 'api-client.updated', $apiClient, $before, $apiClient->fresh()->toArray());

        return redirect()->route('admin.api-clients.index')->with('status', 'تم تحديث عميل API.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function rotateToken(
        Request $request,
        ApiClient $apiClient,
        AdminAuditService $audit,
    ): RedirectResponse {
        abort_unless($apiClient->is_active, 422, 'فعّل عميل API قبل توليد توكن جديد.');

        $accessToken = DB::transaction(function () use ($apiClient): NewAccessToken {
            $apiClient->tokens()->delete();

            return $this->issueToken($apiClient);
        });

        $audit->record($request, 'api-client.token-rotated', $apiClient, null, [
            'expires_at' => $accessToken->accessToken->expires_at,
        ]);

        return back()
            ->with('status', 'تم إلغاء التوكن السابق وتوليد توكن جديد.')
            ->with('plain_api_token', $accessToken->plainTextToken)
            ->with('api_client_name', $apiClient->name);
    }

    public function revokeTokens(
        Request $request,
        ApiClient $apiClient,
        AdminAuditService $audit,
    ): RedirectResponse {
        $revokedTokens = $apiClient->tokens()->delete();
        $audit->record($request, 'api-client.tokens-revoked', $apiClient, null, [
            'revoked_tokens' => $revokedTokens,
        ]);

        return back()->with('status', 'تم إلغاء جميع توكنات هذا الموقع فورًا.');
    }

    private function issueToken(ApiClient $apiClient): NewAccessToken
    {
        return $apiClient->createToken(
            'server-integration',
            [ApiClient::AbilityCreateUsers],
            now()->addDays($apiClient->token_expiration_days),
        );
    }
}
