<div class="student-profile-board">
    <section class="student-profile-card">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </span>
            <h2 class="student-profile-card__title">المدفوعات والطلبات ({{ $orders->count() }})</h2>
        </header>
        <div class="student-profile-card__body">
            <div class="admin-table-wrap">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>المرجع</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="dash-inline-link"><code class="admin-code">{{ $order->reference }}</code></a></td>
                                <td dir="ltr">{{ number_format((float) $order->total, 2) }} ر.س</td>
                                <td>{{ $order->payment_method ? \App\Support\PaymentMethods::label($order->payment_method) : '—' }}</td>
                                <td>
                                    <span @class(['admin-badge', \App\Support\OrderOptions::statusBadgeClass($order->status)])>
                                        {{ \App\Support\OrderOptions::statusLabel($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:1.5rem">
                                    لا توجد مدفوعات مرتبطة.
                                    @if (! $student->user_id && $student->email)
                                        <br><span class="admin-crud-card__meta">يمكن ربط حساب مستخدم بنفس البريد لعرض الطلبات.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->isNotEmpty())
                <div class="admin-filter-actions" style="margin-top:1rem;">
                    <a href="{{ route('admin.orders') }}" class="admin-btn-secondary admin-btn-secondary--sm">كل الطلبات</a>
                </div>
            @endif
        </div>
    </section>
</div>
