<?php

declare(strict_types=1);

namespace AzJezz\Input;

use AzJezz\Input\Exception\BadInputException;
use AzJezz\Input\Exception\TypeException;

interface HydratorInterface
{
    /**
     * Map the given request to the input data-transfer object.
     *
     * @psalm-template T of InputInterface
     *
     * @psalm-param class-string<T>         $input_class
     * @psalm-param array<array-key, mixed> $request
     *
     * @psalm-return T
     *
     * @throws BadInputException If unable to construct the input class from the given request data.
     * @throws TypeException     If the input class contains a property that is either untyped,
     *                           or of a non-supported type.
     */
    public function hydrate(string $input_class, array $request): InputInterface;
}
