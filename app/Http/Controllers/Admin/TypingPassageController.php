<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TypingPassageRequest;
use App\Models\TypingPassage;
use App\Services\AdminAuditService;
use App\Services\TypingPassageCalculator;
use App\TypingPassageLanguage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypingPassageController extends Controller
{
    public function index(Request $request): View
    {
        $passages = TypingPassage::withTrashed()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $query->where('content', 'like', '%'.$request->string('search')->trim()->toString().'%');
            })
            ->when(
                TypingPassageLanguage::tryFrom($request->string('language')->toString()),
                fn (Builder $query, TypingPassageLanguage $language) => $query->where('language', $language),
            )
            ->when($request->string('status')->toString(), function (Builder $query, string $status): void {
                match ($status) {
                    'active' => $query->whereNull('deleted_at')->where('is_active', true),
                    'inactive' => $query->whereNull('deleted_at')->where('is_active', false),
                    'deleted' => $query->whereNotNull('deleted_at'),
                    default => null,
                };
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.typing-passages.index', [
            'passages' => $passages,
            'languages' => TypingPassageLanguage::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.typing-passages.form', [
            'passage' => new TypingPassage,
            'languages' => TypingPassageLanguage::cases(),
        ]);
    }

    public function store(
        TypingPassageRequest $request,
        TypingPassageCalculator $calculator,
        AdminAuditService $audit,
    ): RedirectResponse {
        $passage = TypingPassage::query()->create($this->attributes($request, $calculator));
        $audit->record($request, 'typing-passage.created', $passage, null, $passage->toArray());

        return redirect()->route('admin.typing-passages.index')->with('status', 'تمت إضافة فقرة التدريب.');
    }

    public function edit(TypingPassage $typingPassage): View
    {
        return view('admin.typing-passages.form', [
            'passage' => $typingPassage,
            'languages' => TypingPassageLanguage::cases(),
        ]);
    }

    public function update(
        TypingPassageRequest $request,
        TypingPassage $typingPassage,
        TypingPassageCalculator $calculator,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $typingPassage->toArray();
        $typingPassage->update($this->attributes($request, $calculator));
        $audit->record(
            $request,
            'typing-passage.updated',
            $typingPassage,
            $before,
            $typingPassage->fresh()->toArray(),
        );

        return redirect()->route('admin.typing-passages.index')->with('status', 'تم تحديث فقرة التدريب.');
    }

    public function destroy(
        Request $request,
        TypingPassage $typingPassage,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $typingPassage->toArray();
        $typingPassage->delete();
        $audit->record($request, 'typing-passage.deleted', $typingPassage, $before);

        return back()->with('status', 'تم حذف فقرة التدريب.');
    }

    public function restore(
        Request $request,
        TypingPassage $typingPassage,
        AdminAuditService $audit,
    ): RedirectResponse {
        $typingPassage->restore();
        $audit->record($request, 'typing-passage.restored', $typingPassage, null, $typingPassage->fresh()->toArray());

        return back()->with('status', 'تمت استعادة فقرة التدريب.');
    }

    /** @return array{language: string, content: string, content_hash: string, word_count: int, expected_seconds: int, is_active: bool} */
    private function attributes(
        TypingPassageRequest $request,
        TypingPassageCalculator $calculator,
    ): array {
        $data = $request->validated();
        $expectedSeconds = $data['auto_calculate_time'] ? null : (int) $data['expected_seconds'];

        return [
            'language' => $data['language'],
            ...$calculator->attributes($data['content'], $expectedSeconds),
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
