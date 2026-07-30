<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminAuditService
{
    public function record(
        Request $request,
        string $action,
        Model $subject,
        ?array $beforeValues = null,
        ?array $afterValues = null,
    ): AdminAuditLog {
        return AdminAuditLog::query()->create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'before_values' => $this->safeValues($beforeValues),
            'after_values' => $this->safeValues($afterValues),
            'ip_address' => $request->ip(),
        ]);
    }

    private function safeValues(?array $values): ?array
    {
        return $values === null
            ? null
            : Arr::except($values, ['password', 'remember_token']);
    }
}
