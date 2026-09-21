<?php

test('アプリケーションが正常なレスポンスを返す', function () {
    $this->get('/')->assertOk();
});
