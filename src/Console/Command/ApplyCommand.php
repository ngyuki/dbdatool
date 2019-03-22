<?php
namespace ngyuki\DbdaTool\Console\Command;

use ngyuki\DbdaTool\Comparator;
use ngyuki\DbdaTool\Console\Application;
use ngyuki\DbdaTool\DataSource\ConnectionSourceInterface;
use ngyuki\DbdaTool\SqlGenerator\MySqlGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('apply')->setDescription('Apply schema difference to database');

        $this->addArgument('source', InputArgument::REQUIRED, 'Connection information or schema file for source');
        $this->addArgument('target', InputArgument::OPTIONAL, 'Connection information for target database', '@');

        $this->addOption('--exit-status', '', InputOption::VALUE_NONE, 'Has changed then exit code will be 2');

        $appName = Application::NAME;
        $this->setHelp(
            <<<EOS
Apply schema difference to database

e.g.)
    # apply to database (specified by config.php)
    $appName apply -c config.php schema.json
    
    # apply to database (specified by dsn)
    $appName apply schema.json "mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password"
    
    # apply database to database (specified by config.php and dsn)
    $appName apply @ "mysql:host=192.0.2.123;port=3306;dbname=test;charset=utf8:user:password"
EOS
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $total = microtime(true);

        $source = $this->dataSourceFactory->create($input->getArgument('source'));
        $target = $this->dataSourceFactory->create($input->getArgument('target'));

        if ($target instanceof ConnectionSourceInterface == false) {
            throw new \RuntimeException("`target` should be data source with database connection");
        }

        $diff = (new Comparator())->compare(
            $this->filter->filter($target->getSchema()),
            $this->filter->filter($source->getSchema())
        );

        assert($target instanceof ConnectionSourceInterface);
        $pdo = $target->getConnection();
        $sqls = (new MySqlGenerator($pdo))->diff($diff);

        if (count($sqls) === 0) {
            $output->writeln("\n<question>No difference, database definition is matched.</question>");
            return 0;
        }

        foreach ($sqls as $sql) {
            try {
                if ($output->isVerbose()) {
                    $time = microtime(true);
                    $output->writeln("\n$sql;");
                    $pdo->exec($sql);
                    $output->writeln(sprintf('  <comment>-> %.4fs</comment>', microtime(true) - $time));
                } else {
                    $output->write(".");
                    $pdo->exec($sql);
                }
            } catch (\Exception $ex) {
                $output->writeln("\n");
                $output->writeln("<error>$sql</error>");
                throw $ex;
            } catch (\Throwable $ex) {
                $output->writeln("\n");
                $output->writeln("<error>$sql</error>");
                throw $ex;
            }
        }

        if (!$output->isVerbose()) {
            $output->writeln('');
        }

        $output->writeln(sprintf(
            "\n<bg=green;fg=black>Done, all success (time %.4fs, %d queries)</>",
            microtime(true) - $total,
            count($sqls)
        ));

        return $input->getOption('exit-status') ? 2 : 0;
    }
}
