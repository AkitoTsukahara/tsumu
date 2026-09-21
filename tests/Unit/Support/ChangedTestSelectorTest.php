<?php

declare(strict_types=1);

use Tsumu\Ci\ChangedTestSelector;

require_once dirname(__DIR__, 3).'/.github/select-tests.php';

function selector(?array $testMap = null): ChangedTestSelector
{
    return new ChangedTestSelector(dirname(__DIR__, 3), $testMap);
}

test('ドキュメント変更ではテストを選択しない', function () {
    expect(selector()->select(['README.md']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('テスト不要と明示したファイルではテストを選択しない', function () {
    expect(selector()->select(['routes/console.php']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('ページ変更では対応するFeature・Browserテストだけを選択する', function () {
    expect(selector()->select(['resources/views/welcome.blade.php']))->toBe([
        'backend' => ['tests/Feature/ExampleTest.php'],
        'browser' => ['tests/Browser/HomePageTest.php'],
        'unmapped' => [],
    ]);
});

test('テスト変更ではそのテストファイルだけを選択する', function () {
    expect(selector()->select(['tests/Unit/ExampleTest.php']))->toBe([
        'backend' => ['tests/Unit/ExampleTest.php'],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('共通テスト基盤の変更では全suiteを選択する', function () {
    expect(selector()->select(['composer.lock']))->toBe([
        'backend' => ['tests/DbIntegration', 'tests/Feature', 'tests/Unit'],
        'browser' => ['tests/Browser'],
        'unmapped' => [],
    ]);
});

test('suiteが選択された場合は配下の重複するテストパスを除く', function () {
    expect(selector()->select([
        'app/Providers/AppServiceProvider.php',
        'app/Service/Command/CreateUser.php',
    ]))->toBe([
        'backend' => ['tests/DbIntegration', 'tests/Feature'],
        'browser' => ['tests/Browser'],
        'unmapped' => [],
    ]);
});

test('API変更では関連するAPIテストだけを選択できる', function () {
    $testMap = [[
        'patterns' => ['app/Http/Controllers/Api/EquipmentController.php'],
        'backend' => ['tests/Feature/ExampleTest.php'],
    ]];

    expect(selector($testMap)->select(['app/Http/Controllers/Api/EquipmentController.php']))->toBe([
        'backend' => ['tests/Feature/ExampleTest.php'],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('ユーザー作成Serviceでは関連するFeature・Repositoryテストだけを選択する', function () {
    expect(selector()->select(['app/Service/Command/CreateUser.php']))->toBe([
        'backend' => [
            'tests/DbIntegration/Infra/Persistence/Eloquent/EloquentUserRepositoryTest.php',
            'tests/Feature/Console/CreateUserCommandTest.php',
        ],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('対応表にないアプリケーション変更を報告する', function () {
    expect(selector([])->select(['app/Http/Controllers/Api/UnknownController.php']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => ['app/Http/Controllers/Api/UnknownController.php'],
    ]);
});
