<?php

declare(strict_types=1);

namespace ArchTech\Money\Exceptions;

use Exception;

class InvalidCurrencyException extends Exception
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? 'The currency is invalid');
    }
}
