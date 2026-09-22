<?php

test('ヘルスチェックが正常なレスポンスを返す', function () {
    $this->get('/up')->assertOk();
});
