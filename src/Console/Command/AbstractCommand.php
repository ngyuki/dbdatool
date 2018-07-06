<?php
namespace ngyuki\DbdaTool\Console\Command;

use ngyuki\DbdaTool\Console\ConfigLoader;
use ngyuki\DbdaTool\DataSource\DataSourceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var DataSourceFactory
     */
    protected $dataSourceFactory;

    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->addOptions(array(
            new InputOption('--config', '-c', InputOption::VALUE_OPTIONAL, "Config filename."),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // see http://qiita.com/ngyuki/items/d8db4ab6a954c59ed79d
        if ($output->getVerbosity() == $output::VERBOSITY_NORMAL && $input->getOption('verbose')) {
            $output->setVerbosity($output::VERBOSITY_VERBOSE);
        }

        $this->config = (new ConfigLoader())->load($input->getOption('config'));

        $this->dataSourceFactory = new DataSourceFactory($this->config);
    }
}
