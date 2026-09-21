<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Browser');

pest()->browser()->inChrome();

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('DbIntegration');
