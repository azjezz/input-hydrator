<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class ArrayProperty implements InputInterface
{
    public array $fields;
}
