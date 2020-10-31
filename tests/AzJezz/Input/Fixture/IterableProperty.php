<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class IterableProperty implements InputInterface
{
    public iterable $fields;
}
