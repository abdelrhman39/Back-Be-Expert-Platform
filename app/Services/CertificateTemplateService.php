<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateTemplateService
{
    /** @return array<int, array<string, mixed>> */
    public function defaultElements(): array
    {
        return [
            $this->textElement('title', 'شهادة إتمام برنامج', 255, 90, 610, 70, 34, '#14532d', 800),
            $this->textElement('intro', 'تشهد {{ platform.name_ar }} بأن', 335, 200, 450, 42, 19, '#475569', 500),
            $this->textElement('student-name', '{{ student.name_ar }}', 180, 260, 760, 70, 38, '#111827', 800),
            $this->textElement('program-intro', 'قد أتم بنجاح متطلبات البرنامج', 335, 345, 450, 40, 18, '#64748b', 500),
            $this->textElement('program-name', '{{ program.certificate_name }}', 180, 395, 760, 65, 28, '#14532d', 800),
            $this->textElement('dates', 'خلال الفترة من {{ certificate.start_date }} إلى {{ certificate.end_date }}', 285, 475, 550, 45, 16, '#475569', 500),
            $this->textElement('certificate-code', 'رقم الشهادة: {{ certificate.code }}', 120, 650, 310, 36, 14, '#475569', 600),
            [
                'id' => 'qr-verification',
                'type' => 'qr',
                'x' => 902,
                'y' => 610,
                'width' => 115,
                'height' => 115,
                'rotation' => 0,
                'z_index' => 10,
                'variable' => 'certificate.verify_url',
                'foreground' => '#111827',
                'background' => '#ffffff',
            ],
        ];
    }

    public function create(array $data, User $user): CertificateTemplate
    {
        $template = CertificateTemplate::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'canvas_width' => $data['orientation'] === 'portrait' ? 794 : 1123,
            'canvas_height' => $data['orientation'] === 'portrait' ? 1123 : 794,
            'orientation' => $data['orientation'] ?? 'landscape',
            'elements' => $this->defaultElements(),
            'settings' => $this->defaultSettings(),
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        app(AuditLogService::class)->log(
            'certificate_template.created',
            'إنشاء قالب شهادة: '.$template->name,
            'certificates',
            $user,
            $template,
            $template->name,
        );

        return $template;
    }

    public function saveDesign(
        CertificateTemplate $template,
        array $elements,
        array $settings,
        User $user,
        ?UploadedFile $background = null,
    ): CertificateTemplate {
        $elements = $this->sanitizeElements($elements, $template);

        DB::transaction(function () use ($template, $elements, $settings, $user, $background): void {
            $data = [
                'elements' => $elements,
                'settings' => array_replace($this->defaultSettings(), $settings),
                'version' => $template->version + 1,
                'updated_by' => $user->id,
            ];

            if ($background) {
                $data['background_disk'] = 'public';
                $data['background_path'] = $background->store('certificate-templates/backgrounds', 'public');
            }

            $template->update($data);
        });

        $template = $template->fresh();
        app(AuditLogService::class)->log(
            'certificate_template.updated',
            'تحديث تصميم قالب الشهادة: '.$template->name,
            'certificates',
            $user,
            $template,
            $template->name,
            null,
            ['version' => $template->version, 'elements_count' => count($template->elements ?? [])],
        );

        return $template;
    }

    public function setDefault(CertificateTemplate $template): void
    {
        DB::transaction(function () use ($template): void {
            CertificateTemplate::query()->whereKeyNot($template->id)->update(['is_default' => false]);
            $template->update(['is_default' => true, 'status' => 'active']);
        });
    }

    public function duplicate(CertificateTemplate $template, User $user): CertificateTemplate
    {
        $copy = $template->replicate([
            'slug',
            'is_default',
            'status',
            'version',
            'created_by',
            'updated_by',
        ]);
        $copy->name = $template->name.' — نسخة';
        $copy->slug = $this->uniqueSlug($copy->name);
        $copy->is_default = false;
        $copy->status = 'draft';
        $copy->version = 1;
        $copy->created_by = $user->id;
        $copy->updated_by = $user->id;
        $copy->save();

        return $copy;
    }

    public function delete(CertificateTemplate $template, User $user): void
    {
        if ($template->status === 'active') {
            throw ValidationException::withMessages([
                'template' => 'يجب تعطيل القالب قبل حذفه.',
            ]);
        }

        DB::transaction(function () use ($template): void {
            if ($template->is_default) {
                $template->update(['is_default' => false]);
            }

            $template->delete();
        });

        app(AuditLogService::class)->log(
            'certificate_template.deleted',
            'حذف قالب شهادة: '.$template->name,
            'certificates',
            $user,
            $template,
            $template->name,
        );
    }

    /** @return array<string, mixed> */
    public function defaultSettings(): array
    {
        return [
            'background_color' => '#ffffff',
            'font_family' => 'DejaVu Sans',
            'page_margin' => 0,
            'show_safe_area' => true,
            'locale' => 'ar',
            'direction' => 'rtl',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function sanitizeElements(array $elements, CertificateTemplate $template): array
    {
        if (count($elements) > 100) {
            throw ValidationException::withMessages(['elements' => 'لا يمكن أن يحتوي القالب على أكثر من 100 عنصر.']);
        }

        return collect($elements)
            ->filter(fn ($element) => is_array($element) && in_array($element['type'] ?? null, ['text', 'qr', 'line'], true))
            ->map(function (array $element) use ($template): array {
                $element['id'] = Str::limit((string) ($element['id'] ?? Str::uuid()), 80, '');
                $element['width'] = max(10, min((float) ($element['width'] ?? 100), $template->canvas_width));
                $element['height'] = max(10, min((float) ($element['height'] ?? 40), $template->canvas_height));
                $element['x'] = max(0, min((float) ($element['x'] ?? 0), $template->canvas_width - $element['width']));
                $element['y'] = max(0, min((float) ($element['y'] ?? 0), $template->canvas_height - $element['height']));
                $element['rotation'] = max(-360, min((float) ($element['rotation'] ?? 0), 360));
                $element['z_index'] = max(1, min((int) ($element['z_index'] ?? 1), 999));

                if (($element['type'] ?? null) === 'text') {
                    $element['content'] = Str::limit((string) ($element['content'] ?? ''), 2000, '');
                    $element['font_size'] = max(6, min((float) ($element['font_size'] ?? 18), 160));
                    $element['font_weight'] = max(100, min((int) ($element['font_weight'] ?? 400), 900));
                    $element['line_height'] = max(0.7, min((float) ($element['line_height'] ?? 1.4), 3));
                    $element['letter_spacing'] = max(-10, min((float) ($element['letter_spacing'] ?? 0), 30));
                    $element['align'] = in_array($element['align'] ?? null, ['right', 'center', 'left', 'justify'], true)
                        ? $element['align']
                        : 'center';
                }

                return $element;
            })
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function textElement(
        string $id,
        string $content,
        int $x,
        int $y,
        int $width,
        int $height,
        int $fontSize,
        string $color,
        int $weight,
    ): array {
        return [
            'id' => $id,
            'type' => 'text',
            'content' => $content,
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'rotation' => 0,
            'z_index' => 5,
            'font_family' => 'DejaVu Sans',
            'font_size' => $fontSize,
            'font_weight' => $weight,
            'color' => $color,
            'background' => 'transparent',
            'align' => 'center',
            'direction' => 'rtl',
            'line_height' => 1.35,
            'letter_spacing' => 0,
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'certificate-template';
        $slug = $base;
        $counter = 2;

        while (CertificateTemplate::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
