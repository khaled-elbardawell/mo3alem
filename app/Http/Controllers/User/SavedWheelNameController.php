<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageSavedWheelNameRequest;
use App\Models\SavedWheel;
use App\Services\SavedWheelService;
use Illuminate\Http\RedirectResponse;

class SavedWheelNameController extends Controller
{
    public function store(
        ManageSavedWheelNameRequest $request,
        SavedWheel $savedWheel,
        SavedWheelService $service,
    ): RedirectResponse {
        $updatedSavedWheel = $service->addName(
            $savedWheel,
            $request->validated('name'),
            $request->integer('version'),
        );

        return $this->redirectAfterUpdate($updatedSavedWheel, 'تمت إضافة الاسم إلى القائمة.');
    }

    public function destroy(
        ManageSavedWheelNameRequest $request,
        SavedWheel $savedWheel,
        int $nameIndex,
        SavedWheelService $service,
    ): RedirectResponse {
        $updatedSavedWheel = $service->removeName(
            $savedWheel,
            $nameIndex,
            $request->integer('version'),
        );

        return $this->redirectAfterUpdate($updatedSavedWheel, 'تم حذف الاسم من القائمة.');
    }

    private function redirectAfterUpdate(?SavedWheel $savedWheel, string $message): RedirectResponse
    {
        if (! $savedWheel) {
            return back()->withErrors([
                'name' => 'عُدّلت القائمة من جهاز آخر. حدّث الصفحة ثم أعد المحاولة.',
            ]);
        }

        return back()->with('status', $message);
    }
}
