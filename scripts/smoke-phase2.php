<?php

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();

$response = Livewire\Livewire::actingAs($user)->test('student.profile-page');

$html = $response->html();
echo 'profile-header='.(str_contains($html, 'profile-header') ? 'yes' : 'no').PHP_EOL;
echo 'new-profile-wrapper='.(str_contains($html, 'new-profile-wrapper') ? 'yes' : 'no').PHP_EOL;

$learning = Livewire\Livewire::actingAs($user)->test('student.learning-list-page');
$learningHtml = $learning->html();
echo 'learning profile-header='.(str_contains($learningHtml, 'profile-header') ? 'yes' : 'no').PHP_EOL;

$login = Livewire\Livewire::test('auth.login-page');
echo 'portal-card='.(str_contains($login->html(), 'portal-card') ? 'yes' : 'no').PHP_EOL;

Livewire\Livewire::test('auth.login-page')
    ->set('national_id', '1234567890')
    ->set('password_id', 'password')
    ->call('loginByNationalId');
echo 'login-by-id='.(Illuminate\Support\Facades\Auth::check() ? 'ok' : 'fail').PHP_EOL;
