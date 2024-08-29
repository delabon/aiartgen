<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FeatureTestCase extends TestCase
{
    protected ?string $artDir;

    protected function setUp(): void
    {
        parent::setUp();

        // For some reasons the storage facade does not set the right permissions
        $this->artDir = storage_path(Config::get('services.dirs.arts'));
        File::deleteDirectory($this->artDir);
        File::makeDirectory($this->artDir, force: true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->artDir);

        parent::tearDown();
    }
}
