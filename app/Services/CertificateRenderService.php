<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Support\CertificateVariables;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;

class CertificateRenderService
{
    /** @return array{template: array<string, mixed>, elements: array<int, array<string, mixed>>, background: ?string} */
    public function renderData(Certificate $certificate): array
    {
        $certificate->loadMissing(['template', 'academicStudent.batch.program', 'academicStudent.user', 'issuer']);
        $template = $this->templateSnapshot($certificate);
        $values = $certificate->data_snapshot ?: CertificateVariables::resolve($certificate);
        $elements = collect($template['elements'] ?? [])
            ->map(function (array $element) use ($values): array {
                if (($element['type'] ?? null) === 'text') {
                    $element['rendered_content'] = CertificateVariables::interpolate(
                        (string) ($element['content'] ?? ''),
                        $values,
                    );
                }

                if (($element['type'] ?? null) === 'qr') {
                    $value = (string) ($values[$element['variable'] ?? 'certificate.verify_url'] ?? '');
                    $element['data_uri'] = $this->qrDataUri(
                        $value,
                        (string) ($element['foreground'] ?? '#111827'),
                        (string) ($element['background'] ?? '#ffffff'),
                    );
                }

                return $element;
            })
            ->sortBy('z_index')
            ->values()
            ->all();

        return [
            'template' => $template,
            'elements' => $elements,
            'background' => $this->backgroundSource($template),
        ];
    }

    public function html(Certificate $certificate, bool $forPdf = false): string
    {
        return view('partials.certificates.dynamic', [
            'certificate' => $certificate,
            'render' => $this->renderData($certificate),
            'forPdf' => $forPdf,
        ])->render();
    }

    public function pdf(Certificate $certificate): string
    {
        $render = $this->renderData($certificate);
        $width = (float) ($render['template']['canvas_width'] ?? 1123);
        $height = (float) ($render['template']['canvas_height'] ?? 794);
        $tempDir = storage_path('framework/cache/mpdf');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [$width * 25.4 / 96, $height * 25.4 / 96],
            'orientation' => $width >= $height ? 'L' : 'P',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'tempDir' => $tempDir,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->SetTitle('Certificate '.$certificate->code);
        $mpdf->SetAuthor((string) config('app.name'));
        $mpdf->showImageErrors = false;
        $mpdf->WriteHTML($this->html($certificate, true));

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    public function generateAndStore(Certificate $certificate): Certificate
    {
        $path = 'certificates/'.now()->format('Y/m').'/'.$certificate->code.'.pdf';
        $written = Storage::disk('local')->put($path, $this->pdf($certificate));

        if (! $written) {
            throw new RuntimeException('تعذّر حفظ ملف الشهادة.');
        }

        $certificate->update([
            'pdf_disk' => 'local',
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);

        return $certificate->fresh();
    }

    /** @return array<string, mixed> */
    public function snapshot(CertificateTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'version' => $template->version,
            'canvas_width' => $template->canvas_width,
            'canvas_height' => $template->canvas_height,
            'orientation' => $template->orientation,
            'background_disk' => $template->background_disk,
            'background_path' => $template->background_path,
            'elements' => $template->elements ?? [],
            'settings' => $template->settings ?? [],
        ];
    }

    /** @return array<string, mixed> */
    private function templateSnapshot(Certificate $certificate): array
    {
        if (is_array($certificate->template_snapshot) && $certificate->template_snapshot !== []) {
            return $certificate->template_snapshot;
        }

        if ($certificate->template) {
            return $this->snapshot($certificate->template);
        }

        return [
            'canvas_width' => 1123,
            'canvas_height' => 794,
            'orientation' => 'landscape',
            'background_disk' => 'public',
            'background_path' => null,
            'elements' => app(CertificateTemplateService::class)->defaultElements(),
            'settings' => app(CertificateTemplateService::class)->defaultSettings(),
        ];
    }

    private function backgroundSource(array $template): ?string
    {
        $path = $template['background_path'] ?? null;
        $disk = $template['background_disk'] ?? 'public';

        if (! $path) {
            return null;
        }

        try {
            $storage = Storage::disk($disk);
            if (! $storage->exists($path)) {
                return null;
            }

            $mime = $storage->mimeType($path) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode($storage->get($path));
        } catch (\Throwable) {
            return null;
        }
    }

    private function qrDataUri(string $value, string $foreground, string $background): string
    {
        $writer = new SvgWriter;
        $qrCode = new QrCode(
            data: $value !== '' ? $value : 'invalid-certificate',
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 420,
            margin: 12,
            foregroundColor: $this->color($foreground, new Color(17, 24, 39)),
            backgroundColor: $this->color($background, new Color(255, 255, 255)),
        );

        return $writer->write($qrCode)->getDataUri();
    }

    private function color(string $hex, Color $fallback): Color
    {
        $hex = ltrim($hex, '#');

        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return $fallback;
        }

        return new Color(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }
}
