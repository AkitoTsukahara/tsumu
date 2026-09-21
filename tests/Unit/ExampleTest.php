<?php

use Tests\TestCase;

test('Laravelを起動せずにUnitテストを実行できる', function () {
    expect($this)->not->toBeInstanceOf(TestCase::class);
});
