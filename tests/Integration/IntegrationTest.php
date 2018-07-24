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

        $schema = $dir . '/scheme.json';
        $diff = $dir . '/diff.sql';

        $env->clear()->load($dir . '/done.sql');
        $app = $this->runApp('dump');
        $output = $app->fetch();
        file_put_contents($schema, $output);

        $env->clear()->load($dir . '/init.sql');

        $app = $this->runApp('diff', $schema);
        $output = $app->fetch();
        file_put_contents($diff, $output);

        $this->runApp('apply', $schema);

        $app = $this->runApp('dump');
        $output = $app->fetch();

        if (file_exists($dir . '/expect.sql')) {
            $env->clear()->load($dir . '/expect.sql');
            $expected = $this->runApp('dump')->fetch();
        } else {
            $expected = file_get_contents($schema);
        }
        assertThat($output, equalTo($expected));
    }

    private function runApp()
    {
        $args = array_merge(func_get_args(), $this->args);
        $app = new ApplicationTester(new Application());
        $app->runArgs($args);
        return $app;
    }
}
