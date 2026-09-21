<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('平文パスワードを表示・保存せずにユーザーを作成する', function () {
    $this->artisan('tsumu:user:create')
        ->expectsQuestion('名前', 'Akito')
        ->expectsQuestion('メールアドレス', 'AKITO@example.com')
        ->expectsQuestion('パスワード（8文字以上）', 'secret-password')
        ->expectsQuestion('パスワード（確認）', 'secret-password')
        ->expectsOutput('ユーザーを作成しました。')
        ->doesntExpectOutput('secret-password')
        ->assertSuccessful();

    $user = User::query()->sole();

    expect($user->name)->toBe('Akito')
        ->and($user->email)->toBe('akito@example.com')
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('不正なユーザー情報を拒否する', function (
    string $name,
    string $email,
    string $password,
    string $confirmation,
    string $message,
) {
    $this->artisan('tsumu:user:create')
        ->expectsQuestion('名前', $name)
        ->expectsQuestion('メールアドレス', $email)
        ->expectsQuestion('パスワード（8文字以上）', $password)
        ->expectsQuestion('パスワード（確認）', $confirmation)
        ->expectsOutput($message)
        ->assertFailed();

    expect(User::query()->exists())->toBeFalse();
})->with([
    '名前が未入力' => ['', 'akito@example.com', 'secret-password', 'secret-password', '名前を入力してください。'],
    '名前が長すぎる' => [str_repeat('a', 256), 'akito@example.com', 'secret-password', 'secret-password', '名前は255文字以内で入力してください。'],
    'メールアドレスが未入力' => ['Akito', '', 'secret-password', 'secret-password', 'メールアドレスを入力してください。'],
    'メールアドレスの形式が不正' => ['Akito', 'not-an-email', 'secret-password', 'secret-password', '有効なメールアドレスを入力してください。'],
    'パスワードが未入力' => ['Akito', 'akito@example.com', '', '', 'パスワードを入力してください。'],
    'パスワードが短すぎる' => ['Akito', 'akito@example.com', 'short', 'short', 'パスワードは8文字以上で入力してください。'],
    '確認用パスワードが不一致' => ['Akito', 'akito@example.com', 'secret-password', 'different-password', 'パスワードが一致しません。'],
]);

it('既存ユーザーと重複するメールアドレスを拒否する', function () {
    User::factory()->create(['email' => 'akito@example.com']);

    $this->artisan('tsumu:user:create')
        ->expectsQuestion('名前', 'Another Akito')
        ->expectsQuestion('メールアドレス', 'AKITO@example.com')
        ->expectsQuestion('パスワード（8文字以上）', 'secret-password')
        ->expectsQuestion('パスワード（確認）', 'secret-password')
        ->expectsOutput('このメールアドレスはすでに使用されています。')
        ->assertFailed();

    expect(User::query()->count())->toBe(1);
});
