<?php

declare(strict_types=1);

namespace ArchTech\Money\Exceptions;

use Exception;

class CannotExtractCurrencyException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
