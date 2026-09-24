<?php

use App\Livewire\Exercise\Index;
use Domain\Exercise\BodyPart;
use Domain\Exercise\RecordingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\Exercise;
use Infra\Persistence\Eloquent\Models\User;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/settings/exercises')
        ->assertRedirect(route('login'));
});

it('種目がない場合に空状態を表示する', function () {
    $this->withoutVite();

    $this->actingAs(User::factory()->create())
        ->get('/settings/exercises')
        ->assertOk()
        ->assertSee('種目はまだありません');
});

it('自分の種目だけを安全に表示する', function () {
    $this->withoutVite();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create(['name' => 'レッグプレス機材']);
    Exercise::factory()->forEquipment($equipment)->create([
        'name' => '<script>alert("xss")</script>',
        'primary_target' => BodyPart::Quadriceps,
        'secondary_target' => BodyPart::Glutes,
        'recording_method' => RecordingMethod::WeightAndRepetitions,
    ]);
    Exercise::factory()->forUser($otherUser)->create(['name' => '他ユーザーの種目']);

    $this->actingAs($user)
        ->get('/settings/exercises')
        ->assertOk()
        ->assertSeeText('<script>alert("xss")</script>')
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('レッグプレス機材')
        ->assertSee('重量＋回数')
        ->assertSee('大腿四頭筋')
        ->assertSee('臀部')
        ->assertDontSee('他ユーザーの種目');
});

it('所有する機材を使う重量と回数の種目を登録する', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create(['name' => 'レッグプレス機材']);
    Equipment::factory()->forUser($otherUser)->create(['name' => '他ユーザーの機材']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('レッグプレス機材')
        ->assertDontSee('他ユーザーの機材')
        ->set('form.name', '  レッグプレス  ')
        ->set('form.equipmentId', $equipment->id)
        ->set('form.primaryTarget', 'quadriceps')
        ->set('form.secondaryTarget', 'glutes')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.name', '')
        ->assertSet('form.equipmentId', '')
        ->assertSee('種目を登録しました。')
        ->assertSee('レッグプレス');

    $this->assertDatabaseHas('exercises', [
        'user_id' => $user->id,
        'equipment_id' => $equipment->id,
        'name' => 'レッグプレス',
        'primary_target' => 'quadriceps',
        'secondary_target' => 'glutes',
        'recording_method' => 'weight_repetitions',
    ]);
});

it('任意項目を選ばずに種目を登録する', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', 'スクワット')
        ->set('form.primaryTarget', 'quadriceps')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('種目を登録しました。');

    $this->assertDatabaseHas('exercises', [
        'user_id' => $user->id,
        'equipment_id' => null,
        'name' => 'スクワット',
        'primary_target' => 'quadriceps',
        'secondary_target' => null,
        'recording_method' => 'weight_repetitions',
    ]);
});

it('必須項目が空の場合は種目を登録しない', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', '   ')
        ->call('save')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.primaryTarget' => 'required',
        ])
        ->assertSee('種目名を入力してください。')
        ->assertSee('主対象部位を選択してください。');

    $this->assertDatabaseCount('exercises', 0);
});

it('入力値が選択肢や文字数の制約に反する場合は種目を登録しない', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', str_repeat('あ', 101))
        ->set('form.equipmentId', 'invalid')
        ->set('form.primaryTarget', 'invalid')
        ->set('form.secondaryTarget', 'invalid')
        ->call('save')
        ->assertHasErrors([
            'form.name' => 'max',
            'form.equipmentId' => 'uuid',
            'form.primaryTarget',
            'form.secondaryTarget',
        ])
        ->assertSee('種目名は100文字以内で入力してください。')
        ->assertSee('使用機材を選択肢から選んでください。')
        ->assertSee('主対象部位を選択肢から選んでください。')
        ->assertSee('副対象部位を選択肢から選んでください。');

    $this->assertDatabaseCount('exercises', 0);
});

it('別ユーザーの機材を指定した種目を登録しない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUsersEquipment = Equipment::factory()->forUser($otherUser)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', 'レッグプレス')
        ->set('form.equipmentId', $otherUsersEquipment->id)
        ->set('form.primaryTarget', 'quadriceps')
        ->call('save')
        ->assertNotFound();

    $this->assertDatabaseCount('exercises', 0);
});
