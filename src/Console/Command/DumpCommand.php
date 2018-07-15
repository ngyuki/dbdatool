<?php
namespace ngyuki\DbdaTool\Console\Command;

use ngyuki\DbdaTool\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('dump')->setDescription('Display schema definition file');

        $this->addArgument('source', InputArgument::OPTIONAL, 'Connection information or scheme file', '@');
        $this->addOption('output', '-o', InputOption::VALUE_REQUIRED, 'Output filename');

        $appName = Application::NAME;
        $this->setHelp(
            <<<EOS
Display schema definition file

e.g.)
    # dump scheme file from database (specified by config.php)
    $appName dump -c config.php
    
    # dump scheme file from database (specified by dsn)
    $appName dump "mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password"
EOS
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $this->dataSourceFactory->create($input->getArgument('source'));
        $tables = $this->filter->filter($source->getSchema());

        $dump = json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $filename = $input->getOption('output');
        if ($filename === null) {
            $output->write($dump);
        } else {
            file_put_contents($filename, $dump);
        }
    }
}
