<?php

namespace App\Services;

use App\Models\AcademicStaff;
use App\Models\AcademicStudent;
use App\Models\Cart;
use App\Models\Certificate;
use App\Models\CrmAssignmentRule;
use App\Models\CrmContact;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WishlistItem;
use App\Support\AdminPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserDeletionService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function blockedReason(?User $actor, User $target): ?string
    {
        if (! $actor || ! AdminPermissions::can($actor, 'users.manage')) {
            return 'ليس لديك صلاحية حذف المستخدمين.';
        }

        if ($actor->id === $target->id) {
            return 'لا يمكنك حذف حسابك الحالي أثناء تسجيل الدخول.';
        }

        if (
            Schema::hasColumn('users', 'is_break_glass')
            && (bool) ($target->getAttribute('is_break_glass') ?? false)
        ) {
            return 'هذا حساب استرداد محمي ولا يمكن حذفه من لوحة المستخدمين.';
        }

        if ($target->role === 'admin') {
            $activeAdmins = User::query()
                ->where('role', 'admin')
                ->where('status', 'active')
                ->whereKeyNot($target->id)
                ->count();

            if ($activeAdmins < 1) {
                return 'لا يمكن حذف آخر مسؤول نشط في النظام.';
            }
        }

        return null;
    }

    /**
     * @return array{
     *     user_name: string,
     *     user_email: ?string,
     *     role_label: string,
     *     irreversible: array<int, string>,
     *     cascade: array<int, string>,
     *     unlink: array<int, string>,
     *     notes: array<int, string>
     * }
     */
    public function impact(User $user): array
    {
        $user->loadMissing(['academicStudent', 'academicStaff', 'accessRoles', 'microsoftTeamsConnection']);

        $ordersCount = $user->orders()->count();
        $enrollmentsCount = $user->catalogEnrollments()->count();
        $contractsCount = $user->installmentContracts()->count();
        $ticketsCount = $user->supportTickets()->count();
        $certificatesCount = Certificate::query()->where('user_id', $user->id)->count();
        $crmOwned = CrmContact::query()->where('owner_id', $user->id)->count();
        $crmLinked = CrmContact::query()->where('user_id', $user->id)->count();
        $crmRules = CrmAssignmentRule::query()->where('sales_user_id', $user->id)->count();
        $cartItems = Cart::query()->where('user_id', $user->id)->withCount('items')->get()->sum('items_count');
        $wishlistCount = WishlistItem::query()->where('user_id', $user->id)->count();
        $notificationsCount = $user->notifications()->count();
        $rolesCount = $user->accessRoles->count();
        $directPermissionsCount = $user->directPermissions()->count();

        $cascade = [];
        $unlink = [];

        $cascade[] = 'حذف حساب الدخول نهائياً (الاسم، البريد، الجوال، رقم الهوية، كلمة المرور).';
        $cascade[] = 'إنهاء جميع جلسات تسجيل الدخول المرتبطة بهذا الحساب.';

        if ($rolesCount > 0 || $directPermissionsCount > 0) {
            $cascade[] = sprintf(
                'حذف أدوار الوصول المسندة (%d) والاستثناءات المباشرة للصلاحيات (%d).',
                $rolesCount,
                $directPermissionsCount
            );
        } else {
            $cascade[] = 'إزالة أي أدوار أو صلاحيات ديناميكية مرتبطة بالحساب.';
        }

        if ($ordersCount > 0) {
            $cascade[] = sprintf('حذف طلبات الشراء نهائياً (%d) مع عناصرها وسجلات الدفع المرتبطة.', $ordersCount);
        }

        if ($enrollmentsCount > 0) {
            $cascade[] = sprintf('حذف تسجيلات الدورات التدريبية (الكتالوج) نهائياً (%d).', $enrollmentsCount);
        }

        if ($contractsCount > 0) {
            $cascade[] = sprintf('حذف عقود الأقساط نهائياً (%d) مع جداول السداد والمدفوعات المرتبطة.', $contractsCount);
        }

        if ($cartItems > 0 || Cart::query()->where('user_id', $user->id)->exists()) {
            $cascade[] = $cartItems > 0
                ? sprintf('حذف سلة التسوق وما فيها من عناصر (%d).', $cartItems)
                : 'حذف سلة التسوق المرتبطة بالحساب.';
        }

        if ($wishlistCount > 0) {
            $cascade[] = sprintf('حذف قائمة الأمنيات (%d عنصر).', $wishlistCount);
        }

        if ($notificationsCount > 0) {
            $cascade[] = sprintf('حذف إشعارات الحساب (%d).', $notificationsCount);
        }

        if ($user->microsoftTeamsConnection) {
            $cascade[] = 'حذف ربط حساب Microsoft Teams الخاص بالمستخدم.';
        }

        if ($crmRules > 0) {
            $cascade[] = sprintf('حذف قواعد توزيع CRM المسندة لهذا المستخدم (%d).', $crmRules);
        }

        if ($user->academicStudent) {
            $student = $user->academicStudent;
            $unlink[] = sprintf(
                'فك ربط السجل الأكاديمي للطالب (%s) — يبقى السجل في النظام بدون حساب بوابة، ويتوقف دخوله كطالب.',
                $student->academic_id ?: $student->name_ar
            );
        }

        if ($user->academicStaff) {
            $unlink[] = sprintf(
                'فك ربط سجل الكادر التدريبي (%s) — يبقى السجل بدون حساب بوابة مدرب.',
                $user->academicStaff->name_ar ?? ('#'.$user->academicStaff->id)
            );
        }

        if ($ticketsCount > 0) {
            $unlink[] = sprintf('إزالة ربط تذاكر الدعم (%d) من الحساب — تبقى التذاكر محفوظة بدون مستخدم.', $ticketsCount);
        }

        if ($certificatesCount > 0) {
            $unlink[] = sprintf('إزالة ربط الشهادات الصادرة للحساب (%d) — تبقى سجلات الشهادات في النظام.', $certificatesCount);
        }

        if ($crmLinked > 0) {
            $unlink[] = sprintf('إزالة ربط ملف CRM المرتبط بالحساب (%d).', $crmLinked);
        }

        if ($crmOwned > 0) {
            $unlink[] = sprintf('إزالة إسناد ملكية عملاء CRM (%d) — تبقى العملاء بدون مالك.', $crmOwned);
        }

        return [
            'user_name' => $user->displayName(),
            'user_email' => $user->email,
            'role_label' => match ($user->role) {
                'admin' => 'مسؤول',
                'instructor' => 'مدرب',
                'sales' => 'مبيعات',
                'student' => 'طالب',
                default => $user->role ?: 'غير محدد',
            },
            'irreversible' => [
                'هذا الإجراء نهائي ولا يمكن التراجع عنه بعد التأكيد.',
                'لن يتمكن المستخدم من تسجيل الدخول إلى البوابة أو لوحة التحكم بعد الحذف.',
            ],
            'cascade' => $cascade,
            'unlink' => $unlink,
            'notes' => [
                'السجلات التاريخية التي تشير لهذا المستخدم كمنفّذ إجراء (مثل مراجع طلب أو مصحح واجب) ستُفرَّغ الإشارة إليه دون حذف السجل نفسه.',
            ],
        ];
    }

    public function delete(User $actor, User $target): void
    {
        $reason = $this->blockedReason($actor, $target);

        if ($reason) {
            throw ValidationException::withMessages(['delete' => $reason]);
        }

        $impact = $this->impact($target);
        $snapshot = [
            'id' => $target->id,
            'name' => $target->displayName(),
            'email' => $target->email,
            'role' => $target->role,
            'phone' => $target->phone,
            'national_id' => $target->national_id,
        ];

        try {
            DB::transaction(function () use ($actor, $target, $snapshot, $impact): void {
                $this->auditLog->log(
                    action: 'user.deleted',
                    descriptionAr: 'حذف المستخدم: '.$snapshot['name'].' ('.$snapshot['email'].')',
                    group: 'users',
                    actor: $actor,
                    subject: $target,
                    subjectLabel: $snapshot['name'],
                    oldValues: $snapshot,
                    newValues: [
                        'cascade' => $impact['cascade'],
                        'unlink' => $impact['unlink'],
                    ],
                );

                $this->prepareRelatedRecords($target);

                $target->accessRoles()->detach();
                $target->directPermissions()->detach();
                $target->delete();
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'delete' => 'تعذر حذف المستخدم بسبب ارتباطات بيانات. التفاصيل: '.$e->getMessage(),
            ]);
        }
    }

    protected function prepareRelatedRecords(User $target): void
    {
        AcademicStudent::query()->where('user_id', $target->id)->update(['user_id' => null]);
        AcademicStaff::query()->where('user_id', $target->id)->update(['user_id' => null]);

        if (Schema::hasTable('support_tickets')) {
            SupportTicket::query()->where('user_id', $target->id)->update(['user_id' => null]);
        }

        Certificate::query()->where('user_id', $target->id)->update(['user_id' => null]);
        CrmContact::query()->where('user_id', $target->id)->update(['user_id' => null]);
        CrmContact::query()->where('owner_id', $target->id)->update(['owner_id' => null]);
        CrmAssignmentRule::query()->where('sales_user_id', $target->id)->delete();

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $target->id)->delete();
        }

        if (Schema::hasTable('password_reset_tokens') && filled($target->email)) {
            DB::table('password_reset_tokens')->where('email', $target->email)->delete();
        }
    }
}
