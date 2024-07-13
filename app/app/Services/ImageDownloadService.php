<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ImageNotFoundException;
use App\Exceptions\InvalidImageException;
use ErrorException;

class ImageDownloadService
{
    private const MIMES_AND_EXTS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function __construct(private readonly string $dir)
    {
    }

    /**
     * @throws ImageNotFoundException
     * @throws ErrorException
     * @throws InvalidImageException
     */
    public function download(string $urlOrPath): string
    {
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $content = file_get_contents($urlOrPath);

            if ($content === false) {
                throw new ImageNotFoundException('The image does not exist.');
            }

            restore_error_handler();

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $content);
            finfo_close($finfo);

            if (!in_array($mimeType, array_keys(self::MIMES_AND_EXTS))) {
                throw new InvalidImageException('The path or url is not for an image.');
            }

            $filePath = $this->dir . '/' . uniqid() . '.' . self::MIMES_AND_EXTS[$mimeType];
            file_put_contents($filePath, $content);

            return $filePath;
        } catch (ErrorException $e) {
            restore_error_handler();
            throw new ImageNotFoundException('The image does not exist.');
        }
    }
}
