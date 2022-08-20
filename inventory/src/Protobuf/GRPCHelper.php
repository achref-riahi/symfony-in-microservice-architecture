<?php

namespace App\Protobuf;

class GRPCHelper
{
    /**
     * Remove null value from data array of Protobuf messages to avoid errors.
     *
     * @param array<mixed> $message
     * @return array<mixed>
     */
    public static function messageParser(array $message): array
    {
        return array_filter($message, fn ($item) => !is_null($item));
    }
}
