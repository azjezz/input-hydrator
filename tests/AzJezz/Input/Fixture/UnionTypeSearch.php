<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class UnionTypeSearch implements InputInterface
{
    public string $query;

    public ?int   $limit = 100;

    public null | Filter | string $filter = null;
}
