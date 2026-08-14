<?php

namespace App\Services;

use App\Models\Fellowship;
use App\Support\FormFieldLibrary;
use App\Support\RegistrationApplicationOptions;
use Illuminate\Support\Str;

class FellowshipFormService
{
    /** @return array{allowed_types: string, max_size_mb: int, max_files_per_field: int} */
    public function defaultFileUploadSettings(): array
    {
        return [
            'allowed_types' => 'PDF, DOC, DOCX, JPG, PNG',
            'max_size_mb' => 10,
            'max_files_per_field' => 1,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function defaultFields(): array
    {
        $fields = RegistrationApplicationOptions::fieldsFor('fellowship');
        $fields[] = [
            'key' => 'cv',
            'label' => 'السيرة الذاتية',
            'type' => 'file',
            'required' => true,
            'preset' => 'cv',
        ];

        return $this->normalizeFields($fields);
    }

    /** @return list<array<string, mixed>> */
    public function resolveFields(Fellowship $fellowship): array
    {
        $raw = $fellowship->form_fields;

        if (empty($raw)) {
            return $this->defaultFields();
        }

        return $this->normalizeFields($raw);
    }

    /** @return array{allowed_types: string, max_size_mb: int, max_files_per_field: int} */
    public function resolveFileUploadSettings(Fellowship $fellowship): array
    {
        return array_merge(
            $this->defaultFileUploadSettings(),
            is_array($fellowship->file_upload_settings) ? $fellowship->file_upload_settings : [],
        );
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function saveFields(Fellowship $fellowship, array $fields, ?array $fileUploadSettings = null): Fellowship
    {
        $fellowship->update([
            'form_fields' => $this->normalizeFields($fields),
            'file_upload_settings' => $fileUploadSettings
                ? array_merge($this->defaultFileUploadSettings(), $fileUploadSettings)
                : $fellowship->file_upload_settings,
        ]);

        return $fellowship->fresh();
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function normalizeFields(array $fields): array
    {
        $normalized = [];
        $usedKeys = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $key = Str::snake((string) ($field['key'] ?? 'field'));
            $base = $key;
            $i = 2;

            while (in_array($key, $usedKeys, true)) {
                $key = $base.'_'.$i;
                $i++;
            }

            $usedKeys[] = $key;

            $type = (string) ($field['type'] ?? 'text');
            $options = $field['options'] ?? null;

            if (is_string($options)) {
                $options = match ($options) {
                    'education_levels' => RegistrationApplicationOptions::educationLevels(),
                    'genders' => RegistrationApplicationOptions::genders(),
                    'english_levels' => RegistrationApplicationOptions::englishLevels(),
                    'regions' => RegistrationApplicationOptions::regions(),
                    default => [],
                };
            }

            $normalized[] = array_filter([
                'preset' => $field['preset'] ?? null,
                'key' => $key,
                'label' => trim((string) ($field['label'] ?? $key)),
                'type' => $type,
                'required' => (bool) ($field['required'] ?? false),
                'options' => is_array($options) && $options !== [] ? $options : null,
                'contact' => $field['contact'] ?? null,
                'placeholder' => $field['placeholder'] ?? null,
                'hint' => $field['hint'] ?? null,
                'rows' => isset($field['rows']) ? (int) $field['rows'] : null,
                'min' => $field['min'] ?? null,
                'max' => $field['max'] ?? null,
                'step' => $field['step'] ?? null,
                'col' => isset($field['col']) ? (int) $field['col'] : null,
                'accept' => $field['accept'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $normalized;
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function uniqueKeyForPreset(array $fields, string $preset): string
    {
        $template = FormFieldLibrary::preset($preset);
        $base = Str::snake((string) ($template['key'] ?? $preset));
        $existing = collect($fields)->pluck('key')->all();

        if (! in_array($base, $existing, true)) {
            return $base;
        }

        $i = 2;

        while (in_array($base.'_'.$i, $existing, true)) {
            $i++;
        }

        return $base.'_'.$i;
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function addFromPreset(array $fields, string $preset): array
    {
        $template = FormFieldLibrary::preset($preset);
        $template['key'] = $this->uniqueKeyForPreset($fields, $preset);
        $fields[] = $template;

        return $this->normalizeFields($fields);
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function validationRules(array $fields, array $fileSettings = []): array
    {
        $rules = ['terms' => ['accepted']];
        $maxSizeKb = max(1, (int) ($fileSettings['max_size_mb'] ?? 10)) * 1024;
        $mimes = $this->mimesFromAllowedTypes((string) ($fileSettings['allowed_types'] ?? ''));

        foreach ($fields as $field) {
            $type = $field['type'] ?? 'text';
            $isFile = $type === 'file';
            $key = ($isFile ? 'uploads.' : 'formData.').$field['key'];
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = $type === 'checkbox' ? 'accepted' : 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules = match ($type) {
                'email' => array_merge($fieldRules, ['email', 'max:255']),
                'tel', 'text' => array_merge($fieldRules, ['string', 'max:255']),
                'textarea' => array_merge($fieldRules, ['string', 'max:5000']),
                'number' => array_merge($fieldRules, ['numeric']),
                'date' => array_merge($fieldRules, ['date']),
                'select', 'radio' => array_merge($fieldRules, ['string', 'max:64']),
                'checkbox' => array_merge($fieldRules, ['boolean']),
                'file' => array_merge($fieldRules, [
                    'file',
                    'max:'.$maxSizeKb,
                    'mimes:'.implode(',', $this->fileMimesForField($field, $mimes)),
                ]),
                default => array_merge($fieldRules, ['string']),
            };

            if (isset($field['min'])) {
                $fieldRules[] = 'min:'.$field['min'];
            }

            if (isset($field['max'])) {
                $fieldRules[] = 'max:'.$field['max'];
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function allowedFileTypeOptions(): array
    {
        return [
            'PDF' => 'PDF',
            'DOC' => 'DOC',
            'DOCX' => 'DOCX',
            'JPG' => 'JPG / JPEG',
            'PNG' => 'PNG',
            'GIF' => 'GIF',
            'WEBP' => 'WebP',
            'MP4' => 'MP4',
            'MOV' => 'MOV',
            'AVI' => 'AVI',
        ];
    }

    /** @return list<string> */
    public function parseAllowedTypes(string|array|null $raw): array
    {
        $known = array_keys($this->allowedFileTypeOptions());

        if (is_array($raw)) {
            $tokens = array_map(fn ($v) => strtoupper(trim((string) $v)), $raw);
        } else {
            $tokens = preg_split('/[\s,]+/', strtoupper((string) $raw)) ?: [];
        }

        $selected = [];

        foreach ($tokens as $token) {
            $token = trim($token);

            if ($token !== '' && in_array($token, $known, true)) {
                $selected[] = $token;
            }
        }

        return array_values(array_unique($selected ?: ['PDF', 'DOC', 'DOCX', 'JPG', 'PNG']));
    }

    /** @param  list<string>  $types */
    public function formatAllowedTypes(array $types): string
    {
        $known = array_keys($this->allowedFileTypeOptions());
        $normalized = [];

        foreach ($types as $type) {
            $type = strtoupper(trim((string) $type));

            if ($type !== '' && in_array($type, $known, true)) {
                $normalized[] = $type;
            }
        }

        return implode(', ', array_values(array_unique($normalized)));
    }

    /** @param  list<array<string, mixed>>  $fields */
    public function attributeNames(array $fields): array
    {
        $names = ['terms' => 'الموافقة على الشروط'];

        foreach ($fields as $field) {
            $isFile = ($field['type'] ?? '') === 'file';
            $names[($isFile ? 'uploads.' : 'formData.').$field['key']] = $field['label'];
        }

        return $names;
    }

    /** @return list<string> */
    private function mimesFromAllowedTypes(string $allowed): array
    {
        $map = [
            'pdf' => 'pdf',
            'doc' => 'doc',
            'docx' => 'docx',
            'jpg' => 'jpg,jpeg',
            'jpeg' => 'jpg,jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'mp4' => 'mp4',
            'mov' => 'mov',
            'avi' => 'avi',
        ];

        $mimes = [];

        foreach (preg_split('/[\s,]+/', strtoupper($allowed)) as $token) {
            $token = strtolower(trim($token));

            if ($token === '' || ! isset($map[$token])) {
                continue;
            }

            foreach (explode(',', $map[$token]) as $mime) {
                $mimes[] = $mime;
            }
        }

        return array_values(array_unique($mimes ?: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']));
    }

    /** @param  array<string, mixed>  $field */
    /** @param  list<string>  $defaults */
    /** @return list<string> */
    private function fileMimesForField(array $field, array $defaults): array
    {
        return match ($field['accept'] ?? null) {
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'mov', 'avi'],
            default => $defaults,
        };
    }
}
