<?php
namespace ngyuki\DbdaTool\Console\Command;

use ngyuki\DbdaTool\Comparator;
use ngyuki\DbdaTool\Console\Application;
use ngyuki\DbdaTool\DataSource\ConnectionSourceInterface;
use ngyuki\DbdaTool\SqlGenerator\MySqlGenerator;
use ngyuki\DbdaTool\SqlGenerator\PseudoGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('diff')->setDescription('Display SQL statement applying schema difference');

        $this->addArgument('source', InputArgument::REQUIRED, 'Connection information or schema file');
        $this->addArgument('target', InputArgument::OPTIONAL, 'Connection information or schema file', '@');

        $this->addOption('--exit-status', '', InputOption::VALUE_NONE, 'Has changed then exit code will be 2');

        $appName = Application::NAME;
        $this->setHelp(
            <<<EOS
Display SQL statement applying schema difference

e.g.)
    # difference schema.json and database (specified by config.php)
    $appName diff -c config.php schema.json

    # difference file and file
    $appName diff schema1.json schema2.json

    # difference file and stdin
    cat schema1.json | $appName diff - schema2.json

    # difference database and database (specified by config.php and dsn)
    $appName diff @ "mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password"
EOS
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $this->dataSourceFactory->create($input->getArgument('source'));
        $target = $this->dataSourceFactory->create($input->getArgument('target'));

        $diff = (new Comparator())->compare(
            $this->filter->filter($target->getSchema()),
            $this->filter->filter($source->getSchema())
        );

        $generator = null;

        if ($target instanceof ConnectionSourceInterface) {
            $generator = new MySqlGenerator($target->getConnection());
        } elseif ($source instanceof ConnectionSourceInterface) {
            $generator = new MySqlGenerator($source->getConnection());
        } else {
            $generator = new PseudoGenerator();
        }

        $sqls = $generator->diff($diff);
        if (count($sqls) === 0) {
            return 0;
        }

        foreach ($sqls as $sql) {
            $output->write("\n$sql;\n");
        }

        return $input->getOption('exit-status') ? 2 : 0;
    }
}
