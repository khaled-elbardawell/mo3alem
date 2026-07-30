<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavedWheelUpdateRequest;
use App\Models\SavedWheel;
use App\Services\AdminAuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SavedWheelController extends Controller
{
    public function index(Request $request): View
    {
        $savedWheels = SavedWheel::withTrashed()
            ->with(['user' => fn ($query) => $query->withTrashed()->select('id', 'name', 'email')])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn (Builder $query) => $query
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn (Builder $query) => $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.saved-wheels.index', compact('savedWheels'));
    }

    public function edit(SavedWheel $savedWheel): View
    {
        $savedWheel->load(['user' => fn ($query) => $query->withTrashed()]);

        return view('admin.saved-wheels.edit', compact('savedWheel'));
    }

    public function update(
        SavedWheelUpdateRequest $request,
        SavedWheel $savedWheel,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $savedWheel->toArray();
        $data = $request->validated();

        $savedWheel->update([
            'title' => $data['title'],
            'active_title' => $savedWheel->trashed() ? null : $data['title'],
            'names' => $data['names'],
            'names_count' => count($data['names']),
            'version' => $savedWheel->version + 1,
        ]);

        $audit->record($request, 'saved-wheel.updated', $savedWheel, $before, $savedWheel->fresh()->toArray());

        return redirect()->route('admin.saved-wheels.index')->with('status', 'تم تحديث القائمة.');
    }

    public function destroy(Request $request, SavedWheel $savedWheel, AdminAuditService $audit): RedirectResponse
    {
        $before = $savedWheel->toArray();
        $savedWheel->delete();
        $audit->record($request, 'saved-wheel.deleted', $savedWheel, $before);

        return back()->with('status', 'تم حذف القائمة.');
    }

    public function restore(Request $request, SavedWheel $savedWheel, AdminAuditService $audit): RedirectResponse
    {
        if (SavedWheel::query()
            ->where('user_id', $savedWheel->user_id)
            ->where('active_title', $savedWheel->title)
            ->exists()) {
            throw ValidationException::withMessages([
                'title' => 'لا يمكن الاستعادة لأن لدى المستخدم قائمة نشطة بالاسم نفسه.',
            ]);
        }

        $savedWheel->restore();
        $audit->record($request, 'saved-wheel.restored', $savedWheel, null, $savedWheel->fresh()->toArray());

        return back()->with('status', 'تمت استعادة القائمة.');
    }
}
