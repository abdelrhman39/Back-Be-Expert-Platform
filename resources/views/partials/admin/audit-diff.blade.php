@php
    use App\Support\AuditDiff;

    $diff = AuditDiff::build($log->old_values, $log->new_values, $log->action);
    $changedRows = collect($diff['rows'])->where('change', '!=', 'unchanged');
    $unchangedCount = $diff['summary']['unchanged'];
@endphp

@if ($diff['has_changes'] || $log->old_values || $log->new_values)
    <div class="audit-diff" x-data="{ showAll: false }">
        <div class="audit-diff__toolbar">
            <div class="audit-diff__summary">
                <span class="audit-diff__mode audit-diff__mode--{{ $diff['mode'] }}">
                    <i class="fa-solid fa-{{ $diff['mode'] === 'create' ? 'plus' : ($diff['mode'] === 'delete' ? 'trash' : 'pen') }}"></i>
                    {{ AuditDiff::modeLabel($diff['mode']) }}
                </span>
                @if ($diff['summary']['modified'] > 0)
                    <span class="audit-diff__stat audit-diff__stat--modified">
                        <i class="fa-solid fa-pen"></i> {{ $diff['summary']['modified'] }} معدّل
                    </span>
                @endif
                @if ($diff['summary']['added'] > 0)
                    <span class="audit-diff__stat audit-diff__stat--added">
                        <i class="fa-solid fa-plus"></i> {{ $diff['summary']['added'] }} جديد
                    </span>
                @endif
                @if ($diff['summary']['removed'] > 0)
                    <span class="audit-diff__stat audit-diff__stat--removed">
                        <i class="fa-solid fa-minus"></i> {{ $diff['summary']['removed'] }} محذوف
                    </span>
                @endif
            </div>
            @if ($unchangedCount > 0)
                <button type="button" class="audit-diff__toggle-all" @click="showAll = !showAll">
                    <span x-show="!showAll">عرض الحقول بدون تغيير ({{ $unchangedCount }})</span>
                    <span x-show="showAll" x-cloak>إخفاء الحقول بدون تغيير</span>
                </button>
            @endif
        </div>

        <div class="audit-diff__table-wrap">
            <table class="audit-diff__table">
                <thead>
                    <tr>
                        <th class="audit-diff__th-field">الحقل</th>
                        <th class="audit-diff__th-before">
                            <i class="fa-solid fa-clock-rotate-left"></i> قبل الحدث
                        </th>
                        <th class="audit-diff__th-after">
                            <i class="fa-solid fa-arrow-left-long"></i> بعد الحدث
                        </th>
                        <th class="audit-diff__th-type">نوع التغيير</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($diff['rows'] as $row)
                        @if ($row['change'] === 'unchanged')
                            <tr class="audit-diff__row audit-diff__row--unchanged" x-show="showAll" x-cloak wire:key="diff-{{ $log->id }}-{{ $row['key'] }}-u">
                                <td class="audit-diff__field">
                                    <span class="audit-diff__field-name">{{ $row['label'] }}</span>
                                    <code class="audit-diff__field-key" dir="ltr">{{ $row['key'] }}</code>
                                </td>
                                <td class="audit-diff__cell audit-diff__cell--before">
                                    <span class="audit-diff__value">{{ $row['before'] }}</span>
                                </td>
                                <td class="audit-diff__cell audit-diff__cell--after">
                                    <span class="audit-diff__value">{{ $row['after'] }}</span>
                                </td>
                                <td class="audit-diff__type">
                                    <span class="audit-diff__badge audit-diff__badge--unchanged">{{ AuditDiff::changeLabel($row['change']) }}</span>
                                </td>
                            </tr>
                        @else
                            <tr @class([
                                'audit-diff__row',
                                'audit-diff__row--'.$row['change'],
                            ]) wire:key="diff-{{ $log->id }}-{{ $row['key'] }}">
                                <td class="audit-diff__field">
                                    <span class="audit-diff__field-name">{{ $row['label'] }}</span>
                                    <code class="audit-diff__field-key" dir="ltr">{{ $row['key'] }}</code>
                                </td>
                                <td class="audit-diff__cell audit-diff__cell--before">
                                    @if ($row['change'] === 'added')
                                        <span class="audit-diff__empty">—</span>
                                    @else
                                        <span @class([
                                            'audit-diff__value',
                                            'audit-diff__value--highlight-removed' => in_array($row['change'], ['modified', 'removed'], true),
                                        ])>{{ $row['before'] }}</span>
                                    @endif
                                </td>
                                <td class="audit-diff__cell audit-diff__cell--after">
                                    @if ($row['change'] === 'removed')
                                        <span class="audit-diff__empty">—</span>
                                    @else
                                        <span @class([
                                            'audit-diff__value',
                                            'audit-diff__value--highlight-added' => in_array($row['change'], ['modified', 'added'], true),
                                        ])>{{ $row['after'] }}</span>
                                    @endif
                                </td>
                                <td class="audit-diff__type">
                                    <span @class(['audit-diff__badge', 'audit-diff__badge--'.$row['change']])>
                                        @if ($row['change'] === 'added')
                                            <i class="fa-solid fa-plus"></i>
                                        @elseif ($row['change'] === 'removed')
                                            <i class="fa-solid fa-minus"></i>
                                        @else
                                            <i class="fa-solid fa-arrows-left-right"></i>
                                        @endif
                                        {{ AuditDiff::changeLabel($row['change']) }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($changedRows->isEmpty() && $unchangedCount === 0 && ($log->old_values || $log->new_values))
            <div class="audit-diff__raw">
                <details>
                    <summary>عرض البيانات الخام</summary>
                    <div class="audit-diff__raw-grid">
                        @if ($log->old_values)
                            <pre dir="ltr">{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        @endif
                        @if ($log->new_values)
                            <pre dir="ltr">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    </div>
                </details>
            </div>
        @endif
    </div>
@endif
