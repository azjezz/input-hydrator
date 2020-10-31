<?php

declare(strict_types=1);

namespace AzJezz\Input\Test\Fixture;

use AzJezz\Input\InputInterface;

final class RecaptchaResult implements InputInterface
{
    public bool $success;
    public string $action;
    public float $score;
}
