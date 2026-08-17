<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrTemplateRequest;
use App\Http\Requests\Admin\UpdateQrTemplateRequest;
use App\Models\QrTemplate;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class QrTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $templates = QrTemplate::withTrashed()->ordered()->paginate(20);

        return view('admin.qr-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.qr-templates.form', [
            'template' => new QrTemplate([
                'width' => 1968,
                'height' => 1968,
                'qr_x' => 450,
                'qr_y' => 450,
                'qr_size' => 1000,
                'sort_order' => (QrTemplate::withTrashed()->max('sort_order') ?? 0) + 1,
                'is_active' => true,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreQrTemplateRequest $request,
        AdminAuditService $audit,
    ): RedirectResponse {
        $imagePath = $request->file('image')->store('qr-templates', 'public');

        try {
            $template = QrTemplate::query()->create([
                ...$request->safe()->except('image'),
                'key' => 'qr-template-'.Str::lower((string) Str::ulid()),
                'image_path' => $imagePath,
                'is_builtin' => false,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        $audit->record($request, 'qr-template.created', $template, null, $template->toArray());

        return redirect()->route('admin.qr-templates.index')->with('status', 'تم إنشاء قالب QR.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QrTemplate $qrTemplate): View
    {
        return view('admin.qr-templates.form', ['template' => $qrTemplate]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateQrTemplateRequest $request,
        QrTemplate $qrTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $qrTemplate->toArray();
        $data = $request->safe()->except('image');
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('qr-templates', 'public');
            $data['image_path'] = $newImagePath;
            $data['is_builtin'] = false;
        }

        try {
            $qrTemplate->update($data);
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && ! $before['is_builtin']) {
            Storage::disk('public')->delete($before['image_path']);
        }

        $audit->record($request, 'qr-template.updated', $qrTemplate, $before, $qrTemplate->fresh()->toArray());

        return redirect()->route('admin.qr-templates.index')->with('status', 'تم تحديث قالب QR.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        QrTemplate $qrTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $qrTemplate->toArray();
        $qrTemplate->delete();
        $audit->record($request, 'qr-template.deleted', $qrTemplate, $before);

        return back()->with('status', 'تم حذف قالب QR ويمكن استعادته لاحقًا.');
    }

    public function restore(
        Request $request,
        QrTemplate $qrTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $qrTemplate->restore();
        $audit->record($request, 'qr-template.restored', $qrTemplate, null, $qrTemplate->fresh()->toArray());

        return back()->with('status', 'تمت استعادة قالب QR.');
    }
}
