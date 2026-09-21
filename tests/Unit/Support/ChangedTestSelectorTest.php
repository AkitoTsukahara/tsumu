<?php

declare(strict_types=1);

use Tsumu\Ci\ChangedTestSelector;

require_once dirname(__DIR__, 3).'/.github/select-tests.php';

function selector(?array $testMap = null): ChangedTestSelector
{
    return new ChangedTestSelector(dirname(__DIR__, 3), $testMap);
}

test('documentation changes do not select tests', function () {
    expect(selector()->select(['README.md']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('an explicitly mapped file may require no application tests', function () {
    expect(selector()->select(['routes/console.php']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('a page change selects only its mapped feature and browser tests', function () {
    expect(selector()->select(['resources/views/welcome.blade.php']))->toBe([
        'backend' => ['tests/Feature/ExampleTest.php'],
        'browser' => ['tests/Browser/HomePageTest.php'],
        'unmapped' => [],
    ]);
});

test('a changed test selects that test file only', function () {
    expect(selector()->select(['tests/Unit/ExampleTest.php']))->toBe([
        'backend' => ['tests/Unit/ExampleTest.php'],
        'browser' => [],
        'unmapped' => [],
    ]);
});

test('shared test infrastructure selects every suite', function () {
    expect(selector()->select(['composer.lock']))->toBe([
        'backend' => ['tests/DbIntegration', 'tests/Feature', 'tests/Unit'],
        'browser' => ['tests/Browser'],
        'unmapped' => [],
    ]);
});

test('an API change can select only its related API test', function () {
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

test('an unmapped application change is reported', function () {
    expect(selector([])->select(['app/Http/Controllers/Api/UnknownController.php']))->toBe([
        'backend' => [],
        'browser' => [],
        'unmapped' => ['app/Http/Controllers/Api/UnknownController.php'],
    ]);
});
