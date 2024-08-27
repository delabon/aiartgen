<?php

namespace Tests\Traits\Api\V1;

use Carbon\Carbon;

trait ArtUtils
{
    public function assertArt(array $data, Carbon $date): void
    {
        foreach ($data as $art) {
            $this->assertArrayHasKey('id', $art);
            $this->assertArrayHasKey('title', $art);
            $this->assertArrayHasKey('artist', $art);
            $this->assertArrayHasKey('createdAt', $art);
            $this->assertArrayHasKey('updatedAt', $art);
            $this->assertArrayHasKey('url', $art);
            $this->assertArrayNotHasKey('filename', $art);
            $this->assertUrl($art['url']);
            $this->assertSame($date->format('Y-m-d H:i:s'), $art['createdAt']);
            $this->assertSame($date->format('Y-m-d H:i:s'), $art['updatedAt']);
            $this->assertIsArray($art['artist']);
            $this->assertArrayHasKey('id', $art['artist']);
            $this->assertArrayHasKey('name', $art['artist']);
            $this->assertArrayHasKey('username', $art['artist']);
            $this->assertArrayNotHasKey('password', $art['artist']);
            $this->assertArrayNotHasKey('created_at', $art['artist']);
            $this->assertArrayNotHasKey('updated_at', $art['artist']);
            $this->assertArrayNotHasKey('email', $art['artist']);
        }
    }
}
