<?php
namespace ngyuki\DbdaTool\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const NAME = 'dbdatool';
    const VERSION = '@git-version@ (@git-commit@) @datetime@';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $commands = array();
        $commands[] = new Command\ApplyCommand();
        $commands[] = new Command\DiffCommand();
        $commands[] = new Command\DumpCommand();

        $this->addCommands($commands);
    }
}
