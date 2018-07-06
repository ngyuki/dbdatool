<?php
namespace Test\Helper;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ApplicationTester
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var BufferedOutput
     */
    private $output;

    public function __construct(Application $application)
    {
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $this->application = $application;
    }

    public function run()
    {
        $argv = func_get_args();
        array_unshift($argv, __FILE__);

        $input = new ArgvInput($argv);
        $this->output = new BufferedOutput();

        return $this->application->run($input, $this->output);
    }

    public function runArgs($argv)
    {
        array_unshift($argv, __FILE__);

        $input = new ArgvInput($argv);
        $this->output = new BufferedOutput();

        return $this->application->run($input, $this->output);
    }

    public function fetch()
    {
        return $this->output->fetch();
    }
}
