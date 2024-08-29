<?php

namespace Tests\Unit\Services;

use App\Exceptions\ImageNotFoundException;
use App\Exceptions\InvalidImageException;
use App\Services\ImageDownloadService;
use PHPUnit\Framework\TestCase;

class ImageDownloadServiceTest extends TestCase
{
    private ?string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = '/tmp/aiartgen-test';

        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("{$this->dir}/*.*"));
        rmdir($this->dir);

        parent::tearDown();
    }

    public function test_downloads_image_successfully(): void
    {
        $imageUrl = __DIR__ . '/../../TestFiles/1.png';
        $imageDownloadService = new ImageDownloadService($this->dir);

        $imagePath = $imageDownloadService->download($imageUrl);

        $this->assertTrue(file_exists($imagePath));
        $this->assertGreaterThan(0, filesize($imagePath));
        $this->assertSame('image/png', mime_content_type($imagePath));
        $this->assertSame('png', pathinfo($imagePath, PATHINFO_EXTENSION));

        @unlink($imagePath);
    }

    public function test_downloads_different_type_of_image(): void
    {
        $imageUrl = __DIR__ . '/../../TestFiles/3.jpg';
        $imageDownloadService = new ImageDownloadService($this->dir);

        $imagePath = $imageDownloadService->download($imageUrl);

        $this->assertTrue(file_exists($imagePath));
        $this->assertGreaterThan(0, filesize($imagePath));
        $this->assertSame('image/jpeg', mime_content_type($imagePath));
        $this->assertSame('jpg', pathinfo($imagePath, PATHINFO_EXTENSION));

        @unlink($imagePath);
    }

    public function test_throws_image_not_found_exception_when_path_does_not_exist(): void
    {
        $imageUrl = '/not-found-ever' . uniqid() . '.png';
        $imageDownloadService = new ImageDownloadService($this->dir);

        $this->expectException(ImageNotFoundException::class);
        $this->expectExceptionMessage('The image does not exist.');

        $imageDownloadService->download($imageUrl);
    }

    public function test_throws_image_not_found_exception_when_url_does_not_exist(): void
    {
        $imageUrl = 'https://sadldlsaldlsadllqweierirewjkwerjkrnwenr.test/not-found-ever' . uniqid() . '.png';
        $imageDownloadService = new ImageDownloadService($this->dir);

        $this->expectException(ImageNotFoundException::class);
        $this->expectExceptionMessage('The image does not exist.');

        $imageDownloadService->download($imageUrl);
    }

    public function test_throws_invalid_image_exception_when_path_or_url_is_not_an_image(): void
    {
        $imageUrl = __DIR__ . '/../../TestFiles/2.pdf';
        $imageDownloadService = new ImageDownloadService($this->dir);

        $this->expectException(InvalidImageException::class);
        $this->expectExceptionMessage('The path or url is not for an image.');

        $imageDownloadService->download($imageUrl);
    }
}
