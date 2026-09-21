<?php

test('the initial page opens without JavaScript errors', function () {
    visit('/')
        ->assertSee("Let's get started")
        ->assertNoJavascriptErrors();
});
