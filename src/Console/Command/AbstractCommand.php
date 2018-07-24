<?php
namespace ngyuki\DbdaTool\Console\Command;

use ngyuki\DbdaTool\Console\ConfigLoader;
use ngyuki\DbdaTool\DataSource\DataSourceFactory;
use ngyuki\DbdaTool\SchemaFilter;
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

    /**
     * @var SchemaFilter
     */
    protected $filter;

    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->addOptions(array(
            new InputOption('--config', '-c', InputOption::VALUE_OPTIONAL, "Config filename."),
            new InputOption('--ignore-tables', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Ignore table regex patterns."),
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

        $this->filter = (new SchemaFilter())->setIgnoreTablePattern((array)$input->getOption('ignore-tables'));
    }
}
