<?php

namespace Tests\Unit\Services;

use App\Services\ArtGenerationValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ArtGenerationValidatorTest extends TestCase
{
    public function test_validates_data_successfully(): void
    {
        $validator = new ArtGenerationValidator();

        $result = $validator->validate('Cats Dancing!', 1024, 1024);

        $this->assertTrue($result);
    }

    public function test_throws_invalid_argument_exception_when_prompt_is_less_than_3_chars_long(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The prompt must be between 2 and 255 chars.');

        $validator->validate('a', 1024, 1024);

    }

    public function test_throws_invalid_argument_exception_when_prompt_is_more_than_255_chars_long(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The prompt must be between 2 and 255 chars.');

        $validator->validate(str_repeat('a', 434), 1024, 1024);
    }

    public function test_throws_invalid_argument_exception_when_width_is_less_than_512(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The width must be between 512 and 1024');

        $validator->validate('Best cats ever', 12, 1024);
    }

    public function test_throws_invalid_argument_exception_when_width_is_more_than_1024(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The width must be between 512 and 1024');

        $validator->validate('Best cats ever', 9999, 1024);
    }

    public function test_throws_invalid_argument_exception_when_height_is_less_than_512(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The height must be between 512 and 1024');

        $validator->validate('Best cats ever', 1024, 78);
    }

    public function test_throws_invalid_argument_exception_when_height_is_more_than_1024(): void
    {
        $validator = new ArtGenerationValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The height must be between 512 and 1024');

        $validator->validate('Best cats ever', 1024, 7583);
    }
}
