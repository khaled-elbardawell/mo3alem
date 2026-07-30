<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManageCompetitionNameRequest;
use App\Models\Competition;
use App\Services\CompetitionService;
use Illuminate\Http\RedirectResponse;

class CompetitionNameController extends Controller
{
    public function store(
        ManageCompetitionNameRequest $request,
        Competition $competition,
        CompetitionService $service,
    ): RedirectResponse {
        $updatedCompetition = $service->addName(
            $competition,
            $request->validated('name'),
            $request->integer('version'),
        );

        return $this->redirectAfterUpdate($updatedCompetition, 'تمت إضافة الاسم إلى المسابقة.');
    }

    public function destroy(
        ManageCompetitionNameRequest $request,
        Competition $competition,
        int $nameIndex,
        CompetitionService $service,
    ): RedirectResponse {
        $updatedCompetition = $service->removeName(
            $competition,
            $nameIndex,
            $request->integer('version'),
        );

        return $this->redirectAfterUpdate($updatedCompetition, 'تم حذف الاسم من المسابقة.');
    }

    private function redirectAfterUpdate(?Competition $competition, string $message): RedirectResponse
    {
        if (! $competition) {
            return back()->withErrors([
                'name' => 'عُدّلت المسابقة من جهاز آخر. حدّث الصفحة ثم أعد المحاولة.',
            ]);
        }

        return back()->with('status', $message);
    }
}
