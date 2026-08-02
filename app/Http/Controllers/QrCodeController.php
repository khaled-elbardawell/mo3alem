<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexQrCodeRequest;
use App\Http\Requests\StoreQrCodeRequest;
use App\Http\Requests\UpdateQrCodeRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class QrCodeController extends Controller
{
    public function index(IndexQrCodeRequest $request): JsonResponse
    {
        $qrCodes = $request->user()->qrCodes()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where('title', 'like', '%'.$request->string('search')->trim().'%'),
            )
            ->latest('updated_at')
            ->cursorPaginate(40);

        return response()->json([
            'data' => QrCodeResource::collection($qrCodes->items())->resolve($request),
            'next_cursor' => $qrCodes->nextCursor()?->encode(),
            'has_more' => $qrCodes->hasMorePages(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQrCodeRequest $request, QrCodeService $service): JsonResponse
    {
        $qrCode = $service->create($request->user(), $request->validated(), $request->file('logo'));

        return response()->json([
            'data' => QrCodeResource::make($qrCode)->resolve($request),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QrCode $qrCode): JsonResponse
    {
        Gate::authorize('view', $qrCode);

        return response()->json([
            'data' => QrCodeResource::make($qrCode)->resolve(request()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateQrCodeRequest $request,
        QrCode $qrCode,
        QrCodeService $service,
    ): JsonResponse {
        $updatedQrCode = $service->update($qrCode, $request->validated(), $request->file('logo'));

        if (! $updatedQrCode) {
            return response()->json([
                'message' => 'تم تعديل الرمز من جلسة أخرى. حمّل أحدث نسخة ثم أعد المحاولة.',
                'conflict' => true,
                'data' => QrCodeResource::make($qrCode->fresh())->resolve($request),
            ], 409);
        }

        return response()->json([
            'data' => QrCodeResource::make($updatedQrCode)->resolve($request),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, QrCode $qrCode): Response|RedirectResponse
    {
        Gate::authorize('delete', $qrCode);
        $qrCode->delete();

        return $request->expectsJson()
            ? response()->noContent()
            : redirect()->route('dashboard', ['section' => 'qr'])->with('status', 'تم حذف رمز QR.');
    }
}
