<?php

namespace App\Livewire\Forms;

use Livewire\Form;

final class LoginForm extends Form
{
    public string $email = '';

    public string $password = '';

    /**
     * @return array{email: string, password: string}
     */
    public function validatedCredentials(): array
    {
        $this->email = mb_strtolower(trim($this->email));

        /** @var array{email: string, password: string} $credentials */
        $credentials = $this->validate();

        return $credentials;
    }

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式で入力してください。',
            'password.required' => 'パスワードを入力してください。',
        ];
    }
}
