<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class HomeTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('home');
    }
}
