<?php

test('初期ページがJavaScriptエラーなしで開く', function () {
    visit('/')
        ->assertSee("Let's get started")
        ->assertNoJavascriptErrors();
});
