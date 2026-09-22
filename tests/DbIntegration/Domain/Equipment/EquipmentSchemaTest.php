<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infra\Persistence\Eloquent\Models\User;

it('機材をユーザーに紐づけて保存できる', function () {
    $user = User::factory()->create();

    DB::table('equipment')->insert([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'name' => 'レッグプレス',
        'category' => 'machine',
        'weight_unit' => 'kg',
        'weight_increment' => '9.00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('equipment')->sole())
        ->user_id->toBe($user->id)
        ->name->toBe('レッグプレス')
        ->category->toBe('machine')
        ->weight_unit->toBe('kg');
});

it('定義されていないカテゴリをDB制約で拒否する', function () {
    $user = User::factory()->create();

    DB::table('equipment')->insert([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'name' => 'レッグプレス',
        'category' => 'unknown',
        'weight_unit' => 'kg',
        'weight_increment' => '9.00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('ユーザーを削除すると所有する機材も削除する', function () {
    $user = User::factory()->create();

    DB::table('equipment')->insert([
        'id' => (string) Str::uuid7(),
        'user_id' => $user->id,
        'name' => 'レッグプレス',
        'category' => 'machine',
        'weight_unit' => 'kg',
        'weight_increment' => '9.00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user->delete();

    expect(DB::table('equipment')->count())->toBe(0);
});
