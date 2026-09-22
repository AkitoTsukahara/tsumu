<?php

namespace App\Livewire\Auth;

use App\Service\Command\AuthenticateUser;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('ログイン | Tsumu')]
final class Login extends Component
{
    private const int MAX_ATTEMPTS = 5;

    private const int DECAY_SECONDS = 60;

    public string $email = '';

    public string $password = '';

    public function login(AuthenticateUser $authenticateUser): void
    {
        $this->email = mb_strtolower(trim($this->email));

        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式で入力してください。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        $throttleKey = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', "ログイン試行回数が多すぎます。{$seconds}秒後に再試行してください。");

            return;
        }

        if (! $authenticateUser->handle($credentials['email'], $credentials['password'])) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            $this->reset('password');
            $this->addError('email', 'メールアドレスまたはパスワードが正しくありません。');

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();
        $this->redirectRoute('home', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }

    private function throttleKey(): string
    {
        $email = mb_strtolower(trim($this->email));

        return 'login:'.hash('sha256', $email.'|'.request()->ip());
    }
}
