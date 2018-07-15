<?php
namespace Test\Integration\IgnoreTables;

use Test\Integration\IntegrationTest;

class Test extends IntegrationTest
{
    protected $args = ['--ignore-tables', '^t\d',];
}
