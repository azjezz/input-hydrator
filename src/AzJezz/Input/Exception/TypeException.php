<?php

declare(strict_types=1);

namespace AzJezz\Input\Exception;

use DomainException;

use function sprintf;

final class TypeException extends DomainException implements ExceptionInterface
{
    public static function forUnsupportedPropertyType(string $input, string $property, string $type): TypeException
    {
        $message = sprintf(
            'Property "%s" of "%s" input class has an unsupported type ( "%s" ).',
            $property,
            $input,
            $type,
        );

        return new self($message);
    }

    public static function forMissingPropertyType(string $input, string $property): TypeException
    {
        $message = sprintf('Property "%s" of "%s" input class is not typed.', $input, $property);

        return new self($message);
    }
}
