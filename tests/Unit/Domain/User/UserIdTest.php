<?php

declare(strict_types=1);

use Domain\Shared\Exceptions\InvalidUuidV7IdException;
use Domain\User\UserId;

it('UUIDv7をユーザーIDとして扱える', function () {
    $userId = UserId::fromString('0199A3C7-4C28-7B12-8F65-123456789ABC');

    expect($userId->value)->toBe('0199a3c7-4c28-7b12-8f65-123456789abc');
});

it('UUIDv7以外をユーザーIDとして拒否する', function (string $value) {
    UserId::fromString($value);
})->with([
    '整数' => '1',
    'UUIDv4' => '550e8400-e29b-41d4-a716-446655440000',
    '不正なvariant' => '0199a3c7-4c28-7b12-7f65-123456789abc',
    '任意文字列' => 'invalid',
])->throws(InvalidUuidV7IdException::class);
