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

        $schema = $dir . '/output-schema.json';
        $diff = $dir . '/output-diff.sql';

        $env->clear()->load($dir . '/done.sql');

        $output = $this->runApp('dump')->fetch();
        file_put_contents($schema, $output);

        $env->clear()->load($dir . '/init.sql');

        $output = $this->runApp('diff', $schema)->fetch();
        file_put_contents($diff, $output);

        $this->runApp('apply', $schema);

        if (file_exists($dir . '/expect.sql')) {
            $env->clear()->load($dir . '/expect.sql');
            $expected = $this->runApp('dump')->fetch();
        } else {
            $expected = file_get_contents($schema);
        }

        $output = $this->runApp('dump')->fetch();
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
