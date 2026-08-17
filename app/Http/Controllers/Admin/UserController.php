<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use App\Services\AdminAuditService;
use App\Services\MetricService;
use App\UserRole;
use App\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withTrashed()
            ->withCount('savedWheels')
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn (Builder $query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%'));
            })
            ->when($request->filled('role'), fn (Builder $query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(
        UserStoreRequest $request,
        AdminAuditService $audit,
        MetricService $metrics,
    ): RedirectResponse {
        $user = User::query()->create($request->validated());
        $user->forceFill(['email_verified_at' => now()])->save();

        $metrics->increment('registrations');
        $audit->record($request, 'user.created', $user, null, $user->toArray());

        return redirect()->route('admin.users.index')->with('status', 'تم إنشاء الحساب بنجاح.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(
        UserUpdateRequest $request,
        User $user,
        AdminAuditService $audit,
    ): RedirectResponse {
        $data = $request->validated();

        if ($request->user()->is($user)
            && ($data['role'] !== UserRole::Admin->value || $data['status'] !== UserStatus::Active->value)) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكنك إزالة صلاحيتك أو تعليق حسابك الحالي.',
            ]);
        }

        $before = $user->only(['name', 'email', 'role', 'status', 'email_verified_at']);
        $emailChanged = $user->email !== $data['email'];
        $wasSuspended = $user->status === UserStatus::Suspended;

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (! $wasSuspended && $user->status === UserStatus::Suspended) {
            $this->endSessions($user);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        $audit->record($request, 'user.updated', $user, $before, $user->fresh()->toArray());

        return redirect()->route('admin.users.index')->with('status', 'تم تحديث المستخدم.');
    }

    public function destroy(Request $request, User $user, AdminAuditService $audit): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'لا يمكنك حذف حسابك الحالي.');

        $before = $user->toArray();
        $this->endSessions($user);
        $user->delete();
        $audit->record($request, 'user.deleted', $user, $before);

        return back()->with('status', 'تم حذف المستخدم حذفًا آمنًا.');
    }

    public function restore(Request $request, User $user, AdminAuditService $audit): RedirectResponse
    {
        $user->restore();
        $audit->record($request, 'user.restored', $user, null, $user->fresh()->toArray());

        return back()->with('status', 'تمت استعادة المستخدم.');
    }

    private function endSessions(User $user): void
    {
        DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
    }
}
