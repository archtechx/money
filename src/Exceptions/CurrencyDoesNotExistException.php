<?php

declare(strict_types=1);

namespace ArchTech\Money\Exceptions;

use Exception;

class CurrencyDoesNotExistException extends Exception
{
    public function __construct(string $code)
    {
        parent::__construct("The $code currency does not exist.");
    }
}
