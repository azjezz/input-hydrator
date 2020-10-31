<?php

declare(strict_types=1);

namespace AzJezz\Input\Exception;

use RuntimeException;

final class BadInputException extends RuntimeException implements ExceptionInterface
{
    public static function createForMissingField(string $field): self
    {
        return new self(sprintf('required field "%s" is missing from the request.', $field));
    }

    public static function createForInvalidFieldType(string $field, string $expected, string $actual): self
    {
        return new self(sprintf(
            'field "%s" has an incorrect type of "%s", "%s" was expected.',
            $field,
            $actual,
            $expected,
        ));
    }
}
