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
#[Description('Create a user who can sign in to Tsumu')]
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
            'name' => $this->ask('Name'),
            'email' => $this->ask('Email address'),
            'password' => $this->secret('Password (minimum 8 characters)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];

        $validator = $this->validatorFactory->make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
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
            $this->error('An account with this email address already exists.');

            return self::FAILURE;
        }

        $this->info('Account created.');

        return self::SUCCESS;
    }
}
