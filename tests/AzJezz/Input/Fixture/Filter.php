<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class Filter implements InputInterface
{
    public ?int $maximumPrice;
    public ?int $minimumPrice;
}
