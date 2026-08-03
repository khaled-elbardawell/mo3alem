<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCertificateRequest;
use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CertificateController extends Controller
{
    public function index(IndexCertificateRequest $request): JsonResponse
    {
        $certificates = $request->user()->certificates()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where('title', 'like', '%'.$request->string('search')->trim().'%'),
            )
            ->latest('updated_at')
            ->cursorPaginate(40);

        return response()->json([
            'data' => CertificateResource::collection($certificates->items())->resolve($request),
            'next_cursor' => $certificates->nextCursor()?->encode(),
            'has_more' => $certificates->hasMorePages(),
        ]);
    }

    public function store(StoreCertificateRequest $request, CertificateService $service): JsonResponse
    {
        $certificate = $service->create($request->user(), $request->validated(), $request->file('background'));

        return response()->json([
            'data' => CertificateResource::make($certificate)->resolve($request),
        ], 201);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        Gate::authorize('view', $certificate);

        return response()->json([
            'data' => CertificateResource::make($certificate)->resolve(request()),
        ]);
    }

    public function update(
        UpdateCertificateRequest $request,
        Certificate $certificate,
        CertificateService $service,
    ): JsonResponse {
        $updatedCertificate = $service->update(
            $certificate,
            $request->validated(),
            $request->file('background'),
        );

        if (! $updatedCertificate) {
            return response()->json([
                'message' => 'تم تعديل الشهادة من جلسة أخرى. حمّل أحدث نسخة ثم أعد المحاولة.',
                'conflict' => true,
                'data' => CertificateResource::make($certificate->fresh())->resolve($request),
            ], 409);
        }

        return response()->json([
            'data' => CertificateResource::make($updatedCertificate)->resolve($request),
        ]);
    }

    public function destroy(Request $request, Certificate $certificate): Response|RedirectResponse
    {
        Gate::authorize('delete', $certificate);
        $certificate->delete();

        return $request->expectsJson()
            ? response()->noContent()
            : redirect()->route('dashboard', ['section' => 'certificates'])->with('status', 'تم حذف الشهادة.');
    }
}
