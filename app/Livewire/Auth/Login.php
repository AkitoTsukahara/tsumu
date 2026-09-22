<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
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

    public LoginForm $form;

    public function login(AuthenticateUser $authenticateUser): void
    {
        $credentials = $this->form->validatedCredentials();

        $throttleKey = $this->throttleKey($credentials['email']);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->form->addError('email', "ログイン試行回数が多すぎます。{$seconds}秒後に再試行してください。");

            return;
        }

        if (! $authenticateUser->handle($credentials['email'], $credentials['password'])) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            $this->form->reset('password');
            $this->form->addError('email', 'メールアドレスまたはパスワードが正しくありません。');

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

    private function throttleKey(string $email): string
    {
        return 'login:'.hash('sha256', $email.'|'.request()->ip());
    }
}
