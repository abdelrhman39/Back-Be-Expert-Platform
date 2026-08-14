<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Support\MoyasarSettings;
use Illuminate\Database\Seeder;

class PaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentSetting::get('bank_transfer_instructions_ar') === null) {
            PaymentSetting::set('bank_transfer_instructions_ar', <<<'HTML'
<h6 class="mb-2">بيانات الحساب البنكي</h6>
<ul class="mb-3">
    <li><strong>اسم البنك:</strong> البنك الأهلي السعودي</li>
    <li><strong>اسم المستفيد:</strong> مركز التعلم المستمر - جامعة الامير مقرن</li>
    <li><strong>رقم IBAN:</strong> SA00 0000 0000 0000 0000 0000</li>
</ul>
<p class="mb-2"><strong>خطوات التحويل:</strong></p>
<ol class="mb-0">
    <li>قم بتحويل المبلغ الإجمالي للطلب إلى الحساب أعلاه.</li>
    <li>احتفظ بإيصال التحويل.</li>
    <li>أكّد الطلب من هذه الصفحة — سيتواصل معك فريقنا لتفعيل الدورة بعد التحقق.</li>
</ol>
HTML);
        }

        if (PaymentSetting::get('bank_transfer_instructions_en') === null) {
            PaymentSetting::set('bank_transfer_instructions_en', <<<'HTML'
<h6 class="mb-2">Bank account details</h6>
<ul class="mb-3">
    <li><strong>Bank:</strong> Saudi National Bank</li>
    <li><strong>Beneficiary:</strong> Continuing Learning Center - Muqrin University</li>
    <li><strong>IBAN:</strong> SA00 0000 0000 0000 0000 0000</li>
</ul>
<p class="mb-2"><strong>Transfer steps:</strong></p>
<ol class="mb-0">
    <li>Transfer the order total to the account above.</li>
    <li>Keep your transfer receipt.</li>
    <li>Confirm the order — our team will activate your course after verification.</li>
</ol>
HTML);
        }

        if (PaymentSetting::get(MoyasarSettings::CURRENCY) === null) {
            PaymentSetting::set(MoyasarSettings::CURRENCY, 'SAR');
        }

        if (PaymentSetting::get(MoyasarSettings::ENABLED) === null) {
            PaymentSetting::set(MoyasarSettings::ENABLED, '0');
        }

        $gatewayDefaults = [
            'payment_bank_transfer_enabled' => '1',
            'payment_mada_enabled' => '1',
            'payment_visa_enabled' => '1',
            'payment_mastercard_enabled' => '1',
            'payment_apple_pay_enabled' => '1',
            'payment_tabby_enabled' => '0',
            'payment_tamara_enabled' => '0',
            'payment_platform_installment_enabled' => '1',
        ];

        foreach ($gatewayDefaults as $key => $value) {
            if (PaymentSetting::get($key) === null) {
                PaymentSetting::set($key, $value);
            }
        }
    }
}
