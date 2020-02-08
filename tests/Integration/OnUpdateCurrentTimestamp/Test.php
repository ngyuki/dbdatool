<?php
namespace Test\Integration\OnUpdateCurrentTimestamp;

use Test\Helper\TestEnv;
use Test\Integration\IntegrationTest;

class Test extends IntegrationTest
{
    /**
     * @before
     */
    protected function checkMysqlVersion()
    {
        $env = new TestEnv();
        $ver = $env->pdo()->query('select version()')->fetchColumn();
        if (version_compare($ver, '8.0.0') < 0) {
            $this->markTestSkipped("require MySQL version 8.0.0");
        }
    }
}
