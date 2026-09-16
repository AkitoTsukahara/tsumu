<?php

test('the unit test runner works without booting Laravel', function () {
    expect($this)->not->toBeInstanceOf(Tests\TestCase::class);
});
