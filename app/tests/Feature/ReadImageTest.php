<?php

namespace Tests\Feature;

use App\Models\Art;
use Illuminate\Support\Facades\Config;
use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadImageTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_reads_image_successfully(): void
    {
        $art = Art::factory()->create();
        $mimeType = mime_content_type(storage_path(Config::get('services.dirs.arts')) . '/' . $art->filename);

        $this->get("/image/{$art->id}")
            ->assertOk()
            ->assertHeader('Content-Type', $mimeType);
    }

    public function test_returns_not_found_response_when_image_does_not_exist(): void
    {
        $this->get("/image/894359834975348959")
            ->assertNotFound();
    }
}
