<?php
namespace Test\Integration;

use ngyuki\DbdaTool\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Test\Helper\ApplicationTester;
use Test\Helper\TestEnv;

class ApplyTest extends TestCase
{
    protected $args = [];

    /**
     * @test
     * @dataProvider data
     */
    public function test(string $name, array $test)
    {
        $env = new TestEnv();

        $this->checkEngine($test);

        $args = isset($test['args']) ? $test['args'] : [];

        $dir = __DIR__ . '/output/' . $name;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $schema = $dir . '/schema.json';
        $diff = $dir . '/diff.sql';

        $env->clear();
        foreach ($test['tobe'] as $sql) {
            $env->exec($sql);
        }

        $output = $this->dump();
        file_put_contents($schema, $output);

        $env->clear();
        if (isset($test['asis'])) {
            foreach ($test['asis'] as $sql) {
                $env->exec($sql);
            }
        }

        $output = $this->runApp('diff', $schema, $args)->fetch();
        file_put_contents($diff, $output);

        $this->runApp('apply', $schema, $args);

        $output = $this->dump();

        if (isset($test['expect'])) {
            $env->clear();
            foreach ($test['expect'] as $sql) {
                $env->exec($sql);
            }
            $expected = $this->dump();
        } else {
            $expected = file_get_contents($schema);
        }

        assertThat($output, equalTo($expected));

        $output = $this->runApp('diff', $schema, $args)->fetch();
        assertThat($output, isEmpty());
    }

    public function data()
    {
        $tests = Yaml::parseFile(__DIR__ . '/ApplyTest.yaml');
        $ret = [];
        foreach ($tests as $name => $test) {
            $ret[$name] = [$name, $test];
        }
        return $ret;
    }

    private function checkEngine(array $test)
    {
        if (isset($test['engine'])) {
            foreach ($test['engine'] as $op => $version) {
                $env = new TestEnv();
                $actual = $env->pdo()->query('select version()')->fetchColumn();
                if (!version_compare($actual, $version, $op)) {
                    $this->markTestSkipped("expected MySQL version $op $version ... actual $actual");
                }
            }
        }
    }

    private function runApp($command, $scheme, $args)
    {
        if ($scheme === null) {
            $args = array_merge([$command], $args);
        } else {
            $args = array_merge([$command, $scheme], $args);
        }
        $app = new ApplicationTester(new Application());
        $app->runArgs($args);
        return $app;
    }

    private function dump()
    {
        $app = new ApplicationTester(new Application());
        $app->runArgs(['dump']);
        return $app->fetch();
    }
}
