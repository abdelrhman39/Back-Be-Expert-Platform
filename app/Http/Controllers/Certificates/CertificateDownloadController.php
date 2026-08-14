<?php

namespace App\Http\Controllers\Certificates;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateRenderService;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CertificateDownloadController extends Controller
{
    public function __invoke(Request $request, string $locale, Certificate $certificate, CertificateRenderService $renderer): Response
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($certificate->user_id === $user->id, 403);
        abort_unless(CertificateAccessSettings::portalEnabled(), 404);
        $certificate->loadMissing('academicStudent:id,academic_status,section_id');
        abort_unless(CertificateAccessPolicy::canDownload($certificate), 403, 'تنزيل هذه الشهادة غير متاح حالياً.');

        return $this->download($request, $certificate, $renderer);
    }

    public function admin(Request $request, Certificate $certificate, CertificateRenderService $renderer): Response
    {
        abort_unless($request->user()?->canAdmin('certificates.view'), 403);

        return $this->download($request, $certificate, $renderer);
    }

    private function download(Request $request, Certificate $certificate, CertificateRenderService $renderer): Response
    {
        if ($certificate->isExternal()) {
            abort_unless(
                $certificate->pdf_path
                && $certificate->pdf_disk
                && Storage::disk($certificate->pdf_disk)->exists($certificate->pdf_path),
                404,
                'ملف الشهادة الخارجية غير موجود.',
            );
            abort_unless(
                $certificate->external_file_hash
                && hash_equals(
                    $certificate->external_file_hash,
                    hash('sha256', Storage::disk($certificate->pdf_disk)->get($certificate->pdf_path)),
                ),
                409,
                'تعذّر التحقق من سلامة ملف الشهادة الخارجية.',
            );

            $filename = $certificate->external_file_name ?: 'external-certificate-'.$certificate->code;
            $disposition = $request->boolean('inline') ? 'inline' : 'attachment';
            $fallback = 'external-certificate-'.$certificate->code.'.'.pathinfo($filename, PATHINFO_EXTENSION);

            return Storage::disk($certificate->pdf_disk)->download($certificate->pdf_path, $filename, [
                'Content-Type' => $certificate->external_file_mime ?: 'application/octet-stream',
                'Content-Disposition' => (new ResponseHeaderBag)->makeDisposition($disposition, $filename, $fallback),
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        if (! $certificate->pdf_path || ! $certificate->pdf_disk || ! Storage::disk($certificate->pdf_disk)->exists($certificate->pdf_path)) {
            $certificate = $renderer->generateAndStore($certificate);
        }

        $filename = 'certificate-'.$certificate->code.'.pdf';

        return Storage::disk($certificate->pdf_disk)->download($certificate->pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('inline') ? 'inline' : 'attachment').'; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
