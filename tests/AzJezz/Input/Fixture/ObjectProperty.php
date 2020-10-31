<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class ObjectProperty implements InputInterface
{
    public object $fields;
}
