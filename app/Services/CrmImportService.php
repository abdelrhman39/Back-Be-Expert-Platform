<?php

namespace App\Services;

use App\Models\AcademicProgram;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\CrmImport;
use App\Models\User;
use App\Support\CrmOptions;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class CrmImportService
{
    public function __construct(private CrmAssignmentService $assignment) {}

    /** @param array{program_id?: int|null, owner_id?: int|null, auto_assign?: bool, source?: string|null} $options */
    public function import(UploadedFile $file, User $actor, array $options = []): CrmImport
    {
        $import = CrmImport::query()->create([
            'imported_by' => $actor->id,
            'original_filename' => $file->getClientOriginalName(),
            'options' => $options,
        ]);

        $errors = [];
        $stats = ['total_rows' => 0, 'created_rows' => 0, 'updated_rows' => 0, 'skipped_rows' => 0, 'failed_rows' => 0];

        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (! $handle) {
                throw new RuntimeException('تعذر قراءة الملف.');
            }

            $firstLine = fgets($handle);
            if ($firstLine === false) {
                throw new RuntimeException('الملف فارغ.');
            }
            $firstLine = $this->toUtf8($firstLine);
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            $headers = array_map(fn ($value) => $this->headerKey((string) $value), str_getcsv($firstLine, $delimiter));

            if (! in_array('name', $headers, true)) {
                throw new RuntimeException('يجب أن يحتوي الملف على عمود الاسم (name أو الاسم).');
            }

            $rowNumber = 1;
            while (($raw = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                if ($rowNumber > 20001) {
                    $errors[] = 'تم إيقاف الاستيراد عند 20,000 سجل.';
                    break;
                }

                $raw = array_map(fn ($value) => trim($this->toUtf8((string) $value)), $raw);
                if (count(array_filter($raw, fn ($value) => $value !== '')) === 0) {
                    continue;
                }
                $stats['total_rows']++;

                try {
                    $row = [];
                    foreach ($headers as $index => $header) {
                        if ($header !== '') {
                            $row[$header] = $raw[$index] ?? null;
                        }
                    }
                    $result = $this->upsertRow($row, $import, $actor, $options);
                    $stats[$result.'_rows']++;
                } catch (Throwable $exception) {
                    $stats['failed_rows']++;
                    if (count($errors) < 50) {
                        $errors[] = "السطر {$rowNumber}: ".$exception->getMessage();
                    }
                }
            }
            fclose($handle);

            $import->update([...$stats, 'status' => 'completed', 'errors' => $errors ?: null, 'completed_at' => now()]);
        } catch (Throwable $exception) {
            $import->update([...$stats, 'status' => 'failed', 'errors' => [$exception->getMessage()], 'completed_at' => now()]);
        }

        return $import->refresh();
    }

    /** @return 'created'|'updated'|'skipped' */
    private function upsertRow(array $row, CrmImport $import, User $actor, array $options): string
    {
        $name = trim((string) ($row['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
        $phone = $this->normalizePhone((string) ($row['phone'] ?? ''));
        if ($name === '' || ($email === '' && $phone === '')) {
            throw new RuntimeException('الاسم مع البريد أو الهاتف مطلوب.');
        }

        $contact = CrmContact::query()
            ->when($email !== '', fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email]))
            ->when($email === '' && $phone !== '', fn ($query) => $query->where('phone', $phone))
            ->first();
        $result = $contact ? 'updated' : 'created';
        $contact ??= new CrmContact;

        $programId = $this->programId($row['program'] ?? null, $options['program_id'] ?? null);
        $desiredOwnerId = $options['owner_id'] ?? null;
        $contact->fill([
            'program_id' => $programId ?: $contact->program_id,
            'created_by' => $contact->created_by ?: $actor->id,
            'import_id' => $import->id,
            'source' => CrmOptions::resolveSourceKey(($row['source'] ?? null) ?: ($options['source'] ?? null) ?: 'import'),
            'status' => CrmOptions::resolveStatusKey($row['status'] ?? null, $contact->status ?: CrmOptions::defaultStatusKey()),
            'priority' => $this->allowed($row['priority'] ?? null, array_keys(CrmOptions::priorities()), $contact->priority ?: 'medium'),
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'company' => $row['company'] ?? $contact->company,
            'job_title' => $row['job_title'] ?? $contact->job_title,
            'country' => $row['country'] ?? $contact->country,
            'region' => $row['region'] ?? $contact->region,
            'city' => $row['city'] ?? $contact->city,
            'notes' => $row['notes'] ?? $contact->notes,
            'last_activity_at' => now(),
        ])->save();

        CrmActivity::query()->create([
            'contact_id' => $contact->id,
            'user_id' => $actor->id,
            'type' => 'system',
            'subject' => $result === 'created' ? 'استيراد العميل' : 'تحديث العميل من الاستيراد',
            'content' => ($result === 'created' ? 'تمت إضافة العميل من ملف ' : 'تم تحديث بيانات العميل من ملف ').$import->original_filename,
            'completed_at' => now(),
        ]);
        if ($desiredOwnerId && (int) $contact->owner_id !== (int) $desiredOwnerId) {
            $this->assignment->assign($contact, (int) $desiredOwnerId, $actor, 'توزيع مباشر أثناء استيراد الملف');
        }
        if (! $desiredOwnerId && ($options['auto_assign'] ?? false) && ! $contact->owner_id) {
            $this->assignment->autoAssign($contact, $actor);
        }

        return $result;
    }

    private function programId(?string $value, ?int $fallback): ?int
    {
        if (! filled($value)) {
            return $fallback;
        }

        return AcademicProgram::query()
            ->where('code', $value)
            ->orWhere('name_ar', $value)
            ->orWhere('name_en', $value)
            ->value('id') ?: $fallback;
    }

    private function headerKey(string $header): string
    {
        $header = mb_strtolower(trim(str_replace("\xEF\xBB\xBF", '', $header)));

        return [
            'name' => 'name', 'full_name' => 'name', 'الاسم' => 'name', 'اسم العميل' => 'name',
            'email' => 'email', 'البريد' => 'email', 'البريد الإلكتروني' => 'email',
            'phone' => 'phone', 'mobile' => 'phone', 'الهاتف' => 'phone', 'الجوال' => 'phone',
            'program' => 'program', 'program_code' => 'program', 'البرنامج' => 'program',
            'company' => 'company', 'الشركة' => 'company',
            'job_title' => 'job_title', 'الوظيفة' => 'job_title',
            'country' => 'country', 'الدولة' => 'country',
            'region' => 'region', 'المنطقة' => 'region',
            'city' => 'city', 'المدينة' => 'city',
            'source' => 'source', 'المصدر' => 'source',
            'status' => 'status', 'الحالة' => 'status',
            'priority' => 'priority', 'الأولوية' => 'priority',
            'notes' => 'notes', 'ملاحظات' => 'notes',
        ][$header] ?? '';
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/u', '', trim($phone)) ?: '';

        return str_starts_with($phone, '00') ? '+'.substr($phone, 2) : $phone;
    }

    private function allowed(?string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function toUtf8(string $value): string
    {
        return mb_check_encoding($value, 'UTF-8')
            ? $value
            : mb_convert_encoding($value, 'UTF-8', 'Windows-1256,Windows-1252,ISO-8859-1');
    }
}
