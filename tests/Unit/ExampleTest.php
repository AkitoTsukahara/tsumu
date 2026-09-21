<?php

use Tests\TestCase;

test('the unit test runner works without booting Laravel', function () {
    expect($this)->not->toBeInstanceOf(TestCase::class);
});
