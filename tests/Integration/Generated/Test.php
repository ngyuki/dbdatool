<?php
namespace Test\Integration\Generated;

use Test\Helper\TestEnv;
use Test\Integration\IntegrationTest;

class Test extends IntegrationTest
{
    protected function setUp()
    {
        parent::setUp();

        $env = new TestEnv();
        $row = $env->pdo()->query('select * from information_schema.COLUMNS limit 1')->fetch();
        if (!array_key_exists('GENERATION_EXPRESSION', $row)) {
            $this->markTestSkipped("require MySQL has GENERATION_EXPRESSION");
        }
    }
}
