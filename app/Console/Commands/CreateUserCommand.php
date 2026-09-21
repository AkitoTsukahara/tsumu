<?php

namespace App\Console\Commands;

use App\Service\Command\CreateUser;
use Domain\User\EmailAlreadyInUse;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\Rules\Password;

#[Signature('tsumu:user:create')]
#[Description('Tsumuへログインできるユーザーを作成します')]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly CreateUser $createUser,
        private readonly ValidatorFactory $validatorFactory,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = [
            'name' => $this->ask('名前'),
            'email' => $this->ask('メールアドレス'),
            'password' => $this->secret('パスワード（8文字以上）'),
            'password_confirmation' => $this->secret('パスワード（確認）'),
        ];

        $validator = $this->validatorFactory->make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'name.required' => '名前を入力してください。',
            'name.max' => '名前は255文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        try {
            $this->createUser->handle(
                name: (string) $input['name'],
                email: (string) $input['email'],
                password: (string) $input['password'],
            );
        } catch (EmailAlreadyInUse) {
            $this->error('このメールアドレスはすでに使用されています。');

            return self::FAILURE;
        }

        $this->info('ユーザーを作成しました。');

        return self::SUCCESS;
    }
}
