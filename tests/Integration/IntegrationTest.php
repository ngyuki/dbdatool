<?php
namespace Test\Integration;

use ngyuki\DbdaTool\Console\Application;
use PHPUnit\Framework\TestCase;
use Test\Helper\ApplicationTester;
use Test\Helper\TestEnv;

abstract class IntegrationTest extends TestCase
{
    protected $args = [];

    /**
     * @test
     */
    public function test()
    {
        $dir = dirname((new \ReflectionObject($this))->getFileName());

        $env = new TestEnv();

        $scheme = $dir . '/scheme.json';
        $diff = $dir . '/diff.sql';

        $env->clear()->load($dir . '/done.sql');
        $app = $this->runApp('dump');
        $output = $app->fetch();
        file_put_contents($scheme, $output);

        $env->clear()->load($dir . '/init.sql');

        $app = $this->runApp('diff', $scheme);
        $output = $app->fetch();
        file_put_contents($diff, $output);

        $this->runApp('apply', $scheme);    

        $app = $this->runApp('dump');
        $output = $app->fetch();
        assertThat($output, equalTo(file_get_contents($scheme)));
    }

    private function runApp()
    {
        $args = array_merge(func_get_args(), $this->args);
        $app = new ApplicationTester(new Application());
        $app->runArgs($args);
        return $app;
    }
}
