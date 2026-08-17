<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCertificateTemplateRequest;
use App\Http\Requests\Admin\UpdateCertificateTemplateRequest;
use App\Models\CertificateTemplate;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CertificateTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $templates = CertificateTemplate::withTrashed()->ordered()->paginate(20);

        return view('admin.certificate-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.certificate-templates.form', [
            'template' => new CertificateTemplate([
                'width' => 1123,
                'height' => 794,
                'sort_order' => (CertificateTemplate::withTrashed()->max('sort_order') ?? 0) + 1,
                'is_active' => true,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreCertificateTemplateRequest $request,
        AdminAuditService $audit,
    ): RedirectResponse {
        $imagePath = $request->file('image')->store('certificate-templates', 'public');

        try {
            $template = CertificateTemplate::query()->create([
                ...$request->safe()->except('image'),
                'key' => 'certificate-template-'.Str::lower((string) Str::ulid()),
                'image_path' => $imagePath,
                'is_builtin' => false,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            throw $exception;
        }

        $audit->record($request, 'certificate-template.created', $template, null, $template->toArray());

        return redirect()->route('admin.certificate-templates.index')->with('status', 'تم إنشاء قالب الشهادة.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CertificateTemplate $certificateTemplate): View
    {
        return view('admin.certificate-templates.form', ['template' => $certificateTemplate]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateCertificateTemplateRequest $request,
        CertificateTemplate $certificateTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $certificateTemplate->toArray();
        $data = $request->safe()->except('image');
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('certificate-templates', 'public');
            $data['image_path'] = $newImagePath;
            $data['is_builtin'] = false;
        }

        try {
            $certificateTemplate->update($data);
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath && ! $before['is_builtin']) {
            Storage::disk('public')->delete($before['image_path']);
        }

        $audit->record($request, 'certificate-template.updated', $certificateTemplate, $before, $certificateTemplate->fresh()->toArray());

        return redirect()->route('admin.certificate-templates.index')->with('status', 'تم تحديث قالب الشهادة.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        CertificateTemplate $certificateTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $before = $certificateTemplate->toArray();
        $certificateTemplate->delete();
        $audit->record($request, 'certificate-template.deleted', $certificateTemplate, $before);

        return back()->with('status', 'تم حذف قالب الشهادة ويمكن استعادته لاحقًا.');
    }

    public function restore(
        Request $request,
        CertificateTemplate $certificateTemplate,
        AdminAuditService $audit,
    ): RedirectResponse {
        $certificateTemplate->restore();
        $audit->record($request, 'certificate-template.restored', $certificateTemplate, null, $certificateTemplate->fresh()->toArray());

        return back()->with('status', 'تمت استعادة قالب الشهادة.');
    }
}
