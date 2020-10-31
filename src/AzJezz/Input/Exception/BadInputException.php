<?php

declare(strict_types=1);

namespace AzJezz\Input\Exception;

use RuntimeException;

use function get_class;
use function gettype;
use function is_object;

final class BadInputException extends RuntimeException implements ExceptionInterface
{
    public static function createForMissingField(string $field): self
    {
        return new self(sprintf('required field "%s" is missing from the request.', $field));
    }

    /**
     * @param mixed $actual_value
     */
    public static function createForInvalidFieldTypeFromValue(string $field, string $expected, $actual_value): self
    {
        return new self(sprintf(
            'field "%s" has an incorrect type of "%s", "%s" was expected.',
            $field,
            is_object($actual_value) ? get_class($actual_value) : gettype($actual_value),
            $expected,
        ));
    }
}
