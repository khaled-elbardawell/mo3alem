<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = AdminAuditLog::query()
            ->with(['actor' => fn ($query) => $query->select('id', 'name', 'email')])
            ->when($request->filled('action'), fn (Builder $query) => $query
                ->where('action', 'like', '%'.$request->string('action')->trim().'%'))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }
}
