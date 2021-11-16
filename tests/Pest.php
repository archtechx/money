<?php

use ArchTech\Money\Tests\TestCase;
use Pest\TestSuite;

uses(ArchTech\Money\Tests\TestCase::class)->in('Pest');

function pest(): TestCase
{
    return TestSuite::getInstance()->test;
}
