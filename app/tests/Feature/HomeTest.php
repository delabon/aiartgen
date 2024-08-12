<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_correct_view(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('home');
    }
}
