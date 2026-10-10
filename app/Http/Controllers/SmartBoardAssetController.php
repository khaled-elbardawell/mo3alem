<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmartBoardAssetRequest;
use App\Models\SmartBoard;
use App\Models\SmartBoardAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmartBoardAssetController extends Controller
{
    public function store(StoreSmartBoardAssetRequest $request, SmartBoard $smartBoard): JsonResponse
    {
        if ($smartBoard->assets()->count() >= (int) config('resource_limits.smart_board_assets', 10)) {
            throw ValidationException::withMessages(['image' => 'وصلت إلى الحد الأقصى لصور السبورة.']);
        }

        $image = $request->file('image');
        $path = $image->store("smart-boards/{$smartBoard->id}", 'local');
        $asset = $smartBoard->assets()->create([
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $image->getMimeType(),
            'size' => $image->getSize(),
            'original_name' => $image->getClientOriginalName(),
        ]);

        return response()->json([
            'data' => [
                'id' => $asset->id,
                'url' => route('smart-board-assets.show', $asset),
            ],
        ], 201);
    }

    public function show(SmartBoardAsset $smartBoardAsset): StreamedResponse
    {
        Gate::authorize('view', $smartBoardAsset->smartBoard);

        return Storage::disk($smartBoardAsset->disk)->response(
            $smartBoardAsset->path,
            null,
            ['Cache-Control' => 'private, max-age=3600'],
        );
    }

    public function destroy(SmartBoardAsset $smartBoardAsset): JsonResponse
    {
        Gate::authorize('update', $smartBoardAsset->smartBoard);
        Storage::disk($smartBoardAsset->disk)->delete($smartBoardAsset->path);
        $smartBoardAsset->delete();

        return response()->json(['message' => 'تم حذف الصورة.']);
    }
}
