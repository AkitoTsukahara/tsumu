<?php

test('未認証では初期ページからログイン画面へ移動する', function () {
    visit('/')
        ->assertPathIs('/login')
        ->assertSee('ログイン')
        ->assertNoJavascriptErrors();
});
