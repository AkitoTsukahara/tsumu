<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Infra\Persistence\Eloquent\Models\User;

uses(RefreshDatabase::class);

it('creates an account without exposing or storing the plain password', function () {
    $this->artisan('tsumu:user:create')
        ->expectsQuestion('Name', 'Akito')
        ->expectsQuestion('Email address', 'AKITO@example.com')
        ->expectsQuestion('Password (minimum 8 characters)', 'secret-password')
        ->expectsQuestion('Confirm password', 'secret-password')
        ->expectsOutput('Account created.')
        ->doesntExpectOutput('secret-password')
        ->assertSuccessful();

    $user = User::query()->sole();

    expect($user->name)->toBe('Akito')
        ->and($user->email)->toBe('akito@example.com')
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('rejects invalid account details', function (
    string $name,
    string $email,
    string $password,
    string $confirmation,
    string $message,
) {
    $this->artisan('tsumu:user:create')
        ->expectsQuestion('Name', $name)
        ->expectsQuestion('Email address', $email)
        ->expectsQuestion('Password (minimum 8 characters)', $password)
        ->expectsQuestion('Confirm password', $confirmation)
        ->expectsOutput($message)
        ->assertFailed();

    expect(User::query()->exists())->toBeFalse();
})->with([
    'missing name' => ['', 'akito@example.com', 'secret-password', 'secret-password', 'Name is required.'],
    'long name' => [str_repeat('a', 256), 'akito@example.com', 'secret-password', 'secret-password', 'Name must not exceed 255 characters.'],
    'missing email' => ['Akito', '', 'secret-password', 'secret-password', 'Email address is required.'],
    'invalid email' => ['Akito', 'not-an-email', 'secret-password', 'secret-password', 'Enter a valid email address.'],
    'missing password' => ['Akito', 'akito@example.com', '', '', 'Password is required.'],
    'short password' => ['Akito', 'akito@example.com', 'short', 'short', 'Password must be at least 8 characters.'],
    'unconfirmed password' => ['Akito', 'akito@example.com', 'secret-password', 'different-password', 'Passwords do not match.'],
]);

it('rejects an email address that already belongs to an account', function () {
    User::factory()->create(['email' => 'akito@example.com']);

    $this->artisan('tsumu:user:create')
        ->expectsQuestion('Name', 'Another Akito')
        ->expectsQuestion('Email address', 'AKITO@example.com')
        ->expectsQuestion('Password (minimum 8 characters)', 'secret-password')
        ->expectsQuestion('Confirm password', 'secret-password')
        ->expectsOutput('An account with this email address already exists.')
        ->assertFailed();

    expect(User::query()->count())->toBe(1);
});
