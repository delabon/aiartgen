<?php

namespace Tests\Traits;

trait ProvidesInvalidApiKeys
{
    public static function invalidApiKeyDataProvider (): array
    {
        return [
            'Empty api key' => [
                'apiKey' => ''
            ],
            'Invalid api key' => [
                'apiKey' => 'invalid apu key'
            ],
        ];
    }
}
