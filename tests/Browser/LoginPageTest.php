<?php

test('Android相当の画面幅でログインフォームを操作できる', function () {
    visit('/login')
        ->resize(360, 800)
        ->assertSee('ログイン')
        ->assertVisible('#email')
        ->assertVisible('#password')
        ->assertVisible('button[type="submit"]')
        ->type('email', 'nobody@example.com')
        ->type('password', 'password')
        ->assertValue('email', 'nobody@example.com')
        ->assertValue('password', 'password')
        ->assertNoJavascriptErrors();
});
