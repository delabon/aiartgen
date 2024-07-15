<?php

namespace App\Services;

use InvalidArgumentException;

class ArtGenerationValidator
{
    public function validate(string $prompt, int $width, int $height): bool
    {
        $this->validatePromptLength($prompt);
        $this->validateSize($width, 'width');
        $this->validateSize($height, 'height');

        return true;
    }

    private function validatePromptLength(string $prompt): void
    {
        $length = strlen($prompt);

        if ($length < 2 || $length > 255) {
            throw new InvalidArgumentException('The prompt must be between 2 and 255 chars.');
        }
    }

    private function validateSize(int $size, string $key): void
    {
        if ($size < 512 || $size > 1024) {
            throw new InvalidArgumentException("The {$key} must be between 512 and 1024");
        }
    }
}
