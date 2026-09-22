<?php

use App\Livewire\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('未認証ユーザーにログイン画面を表示する', function () {
    $this->withoutVite();

    $this->get('/login')
        ->assertOk()
        ->assertSee('前回の自分を基準に、今日も少しずつ積み重ねよう。');
});

it('認証済みユーザーをログイン画面から移動させる', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect(route('today'));
});

it('必須項目とメールアドレス形式を検証する', function () {
    Livewire::test(Login::class)
        ->call('login')
        ->assertHasErrors([
            'form.email' => 'required',
            'form.password' => 'required',
        ])
        ->set('form.email', 'invalid-address')
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasErrors(['form.email' => 'email']);
});

it('正しい認証情報でログインしセッションを再生成する', function () {
    $user = User::factory()->create([
        'email' => 'akito@example.com',
        'password' => Hash::make('secret-password'),
    ]);
    $previousSessionId = session()->getId();

    Livewire::test(Login::class)
        ->set('form.email', ' AKITO@example.com ')
        ->set('form.password', 'secret-password')
        ->call('login')
        ->assertRedirect(route('today'));

    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($previousSessionId);
});

it('誤った認証情報では共通エラーを表示する', function () {
    User::factory()->create([
        'email' => 'akito@example.com',
        'password' => Hash::make('secret-password'),
    ]);

    Livewire::test(Login::class)
        ->set('form.email', 'akito@example.com')
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email'])
        ->assertSee('メールアドレスまたはパスワードが正しくありません。')
        ->assertSet('form.password', '');

    $this->assertGuest();
});

it('連続して失敗したログインを一時的に制限する', function () {
    $component = Livewire::test(Login::class)
        ->set('form.email', 'akito@example.com');

    foreach (range(1, 5) as $attempt) {
        $component
            ->set('form.password', "wrong-password-{$attempt}")
            ->call('login');
    }

    $component
        ->set('form.password', 'wrong-password-6')
        ->call('login')
        ->assertSee('ログイン試行回数が多すぎます。');

    $this->assertGuest();
});
