<?php

declare(strict_types=1);

namespace ArchTech\Money\Exceptions;

use ArchTech\Money\CurrencyManager;
use Exception;

class MissingCurrencyManagerExtensionException extends Exception
{
    public function __construct(string $className)
    {
        parent::__construct("Missing extension: The {$className} must extend ".CurrencyManager::class.".");
    }
}