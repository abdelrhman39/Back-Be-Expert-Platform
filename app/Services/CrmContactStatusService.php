<?php

namespace App\Services;

use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Models\User;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CrmContactStatusService
{
    public function change(
        CrmContact $contact,
        string $newStatus,
        User $actor,
        ?string $lostReason = null,
        ?UploadedFile $receipt = null,
    ): bool {
        abort_unless(CrmAccess::canChangeStatus($actor), 403);
        abort_unless(CrmAccess::canAccessContact($actor, $contact), 403);
        abort_unless(array_key_exists($newStatus, CrmOptions::statuses(false)), 422);

        if (CrmOptions::requiresPaymentReceipt($newStatus) && ! $receipt && ! $contact->hasPaymentReceipt()) {
            throw ValidationException::withMessages([
                'paymentReceipt' => 'أرفق إيصال التحويل أو السداد قبل تحديث الحالة إلى «تم السداد».',
            ]);
        }

        $oldStatus = $contact->status;
        $receiptMeta = $receipt ? $this->storeReceipt($contact, $receipt, $actor) : [];

        if ($oldStatus === $newStatus && $receiptMeta === []) {
            return false;
        }

        app(AcademicEnrollmentLifecycleService::class)
            ->assertCrmPaidReversalAllowed($contact, $newStatus, $lostReason);

        $payload = [
            'status' => $newStatus,
            'lost_reason' => CrmOptions::isLost($newStatus) ? ($lostReason ?: null) : (
                ($oldStatus === 'paid' || CrmOptions::isWon($oldStatus)) && $newStatus !== 'paid'
                    ? ($lostReason ?: $contact->lost_reason)
                    : $contact->lost_reason
            ),
            'converted_at' => CrmOptions::isWon($newStatus) ? ($contact->converted_at ?: now()) : null,
            'lost_at' => CrmOptions::isLost($newStatus) ? ($contact->lost_at ?: now()) : null,
            'paid_at' => $newStatus === 'paid' ? ($contact->paid_at ?: now()) : ($newStatus === 'awaiting_payment' ? null : $contact->paid_at),
            'last_activity_at' => now(),
        ];

        if ($receiptMeta !== []) {
            $payload = array_merge($payload, $receiptMeta);
        }

        $contact->update($payload);

        if ($oldStatus !== $newStatus) {
            CrmActivity::query()->create([
                'contact_id' => $contact->id,
                'user_id' => $actor->id,
                'type' => 'status_change',
                'subject' => 'تغيير مرحلة العميل',
                'content' => CrmOptions::statusLabel($oldStatus).' ← '.CrmOptions::statusLabel($newStatus)
                    .($lostReason && ($oldStatus === 'paid' || CrmOptions::isWon($oldStatus)) ? ' — السبب: '.$lostReason : ''),
                'completed_at' => now(),
                'metadata' => ['from' => $oldStatus, 'to' => $newStatus, 'reason' => $lostReason],
            ]);
            app(CrmAuditService::class)->statusChanged($contact, $oldStatus, $newStatus, $actor);
        }

        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            app(AcademicEnrollmentLifecycleService::class)
                ->activateFromCrmPaid($contact->fresh(), $actor);
        }

        return true;
    }

    public function attachReceipt(CrmContact $contact, UploadedFile $receipt, User $actor): void
    {
        abort_unless(CrmAccess::canChangeStatus($actor) || CrmAccess::canUpdate($actor), 403);
        abort_unless(CrmAccess::canAccessContact($actor, $contact), 403);

        $meta = $this->storeReceipt($contact, $receipt, $actor);
        $payload = array_merge($meta, ['last_activity_at' => now()]);
        if ($contact->status === 'paid' && ! $contact->paid_at) {
            $payload['paid_at'] = now();
        }
        $contact->update($payload);
    }

    /** @return array{payment_receipt_path: string, payment_receipt_name: string, payment_receipt_uploaded_at: \Illuminate\Support\Carbon} */
    private function storeReceipt(CrmContact $contact, UploadedFile $receipt, User $actor): array
    {
        if ($contact->payment_receipt_path) {
            Storage::disk('local')->delete($contact->payment_receipt_path);
        }

        $path = $receipt->store('crm/payment-receipts/'.$contact->id, 'local');

        CrmActivity::query()->create([
            'contact_id' => $contact->id,
            'user_id' => $actor->id,
            'type' => 'note',
            'subject' => 'إرفاق إيصال سداد',
            'content' => 'تم رفع إيصال التحويل/السداد: '.$receipt->getClientOriginalName(),
            'completed_at' => now(),
            'metadata' => [
                'receipt_path' => $path,
                'receipt_name' => $receipt->getClientOriginalName(),
            ],
        ]);

        return [
            'payment_receipt_path' => $path,
            'payment_receipt_name' => $receipt->getClientOriginalName(),
            'payment_receipt_uploaded_at' => now(),
        ];
    }
}
