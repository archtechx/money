<?php

namespace ArchTech\Money\Tests;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use ArchTech\Money\MoneyServiceProvider;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MoneyServiceProvider::class,
        ];
    }
}
