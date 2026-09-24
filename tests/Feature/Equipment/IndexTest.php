<?php

use App\Livewire\Equipment\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infra\Persistence\Eloquent\Models\Equipment;
use Infra\Persistence\Eloquent\Models\User;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('未認証ユーザーをログイン画面へ移動させる', function () {
    $this->get('/settings/equipment')
        ->assertRedirect(route('login'));
});

it('機材がない場合に空状態を表示する', function () {
    $this->withoutVite();

    $this->actingAs(User::factory()->create())
        ->get('/settings/equipment')
        ->assertOk()
        ->assertSee('機材はまだありません');
});

it('自分の機材だけを安全に表示する', function () {
    $this->withoutVite();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Equipment::factory()->forUser($user)->create([
        'name' => '<script>alert("xss")</script>',
        'weight_increment' => '9.00',
    ]);
    Equipment::factory()->forUser($otherUser)->create(['name' => '他ユーザーの機材']);

    $this->actingAs($user)
        ->get('/settings/equipment')
        ->assertOk()
        ->assertSeeText('<script>alert("xss")</script>')
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('9.00 kg')
        ->assertDontSee('他ユーザーの機材');
});

it('自分の機材を登録して一覧に表示する', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', '  レッグプレス  ')
        ->set('form.category', 'machine')
        ->set('form.weightUnit', 'kg')
        ->set('form.weightIncrement', '9')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.name', '')
        ->assertSet('form.weightIncrement', '')
        ->assertSee('機材を登録しました。')
        ->assertSee('レッグプレス')
        ->assertSee('9.00 kg');

    $this->assertDatabaseHas('equipment', [
        'user_id' => $user->id,
        'name' => 'レッグプレス',
        'category' => 'machine',
        'weight_unit' => 'kg',
        'weight_increment' => 9,
    ]);
});

it('必須項目が空の場合は登録しない', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', '   ')
        ->set('form.weightIncrement', '   ')
        ->call('save')
        ->assertHasErrors(['form.name' => 'required', 'form.weightIncrement' => 'required'])
        ->assertSee('機材名を入力してください。')
        ->assertSee('重量の刻み幅を入力してください。');

    $this->assertDatabaseCount('equipment', 0);
});

it('選択肢と重量の刻み幅が不正な場合は登録しない', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', 'レッグプレス')
        ->set('form.category', 'invalid')
        ->set('form.weightUnit', 'lb')
        ->set('form.weightIncrement', '1000')
        ->call('save')
        ->assertHasErrors(['form.category', 'form.weightUnit', 'form.weightIncrement'])
        ->assertSee('カテゴリを選択肢から選んでください。')
        ->assertSee('重量単位を選択肢から選んでください。')
        ->assertSee('重量の刻み幅は0.01〜999.99の範囲で、小数2桁まで入力してください。');

    $this->assertDatabaseCount('equipment', 0);
});

it('自分の機材をフォームへ読み込んで更新できる', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create([
        'name' => 'レッグプレス',
        'category' => 'machine',
        'weight_unit' => 'kg',
        'weight_increment' => '9.00',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('edit', $equipment->id)
        ->assertSet('editingEquipmentId', $equipment->id)
        ->assertSet('form.name', 'レッグプレス')
        ->assertSet('form.category', 'machine')
        ->assertSet('form.weightUnit', 'kg')
        ->assertSet('form.weightIncrement', '9.00')
        ->set('form.name', ' レッグプレス45 ')
        ->set('form.category', 'free_weight')
        ->set('form.weightIncrement', '4.5')
        ->call('update')
        ->assertHasNoErrors()
        ->assertSet('editingEquipmentId', null)
        ->assertSee('機材を更新しました。')
        ->assertSee('レッグプレス45')
        ->assertSee('4.50 kg');

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'user_id' => $user->id,
        'name' => 'レッグプレス45',
        'category' => 'free_weight',
        'weight_unit' => 'kg',
        'weight_increment' => 4.5,
    ]);
});

it('別ユーザーの機材を編集フォームへ読み込めない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $equipment = Equipment::factory()->forUser($otherUser)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('edit', $equipment->id)
        ->assertNotFound();

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'user_id' => $otherUser->id,
        'name' => $equipment->name,
    ]);
});

it('編集内容が不正な場合は機材を更新しない', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create([
        'name' => 'レッグプレス',
        'weight_increment' => '9.00',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('edit', $equipment->id)
        ->set('form.name', '   ')
        ->set('form.weightIncrement', '0')
        ->call('update')
        ->assertHasErrors(['form.name' => 'required', 'form.weightIncrement' => 'not_in'])
        ->assertSee('機材名を入力してください。')
        ->assertSee('重量の刻み幅は0より大きい値を入力してください。');

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'name' => 'レッグプレス',
        'weight_increment' => 9,
    ]);
});

it('編集対象が未選択の場合は機材を更新しない', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->forUser($user)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', '変更後')
        ->set('form.weightIncrement', '5')
        ->call('update')
        ->assertNotFound();

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'name' => $equipment->name,
    ]);
});
