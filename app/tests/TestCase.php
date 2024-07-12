<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function assertUrl(string $str): void
    {
        $this->assertTrue(filter_var($str, FILTER_VALIDATE_URL) !== false, sprintf('Failed asserting that %s is an URL.', $str));
    }
}
