<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;
use stdClass;

final class NonInputProperty implements InputInterface
{
    public stdClass $something;
}
