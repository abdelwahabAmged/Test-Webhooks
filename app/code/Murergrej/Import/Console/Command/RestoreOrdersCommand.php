<?php

namespace Murergrej\Import\Console\Command;

use Murergrej\Import\Model\OrderImportProcessorFactory;
use Murergrej\Import\Model\Mysql\ConnectionSettingsFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreOrdersCommand extends Command
{
    /**
     * @var OrderImportProcessorFactory
     */
    protected $orderImportProcessorFactory;

    /**
     * @var ConnectionSettingsFactory
     */
    protected $connectionSettingsFactory;

    public function __construct(
        OrderImportProcessorFactory $orderImportProcessorFactory,
        ConnectionSettingsFactory $connectionSettingsFactory
    ) {
        $this->orderImportProcessorFactory = $orderImportProcessorFactory;
        $this->connectionSettingsFactory = $connectionSettingsFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('murergrej:import:orders')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('init_statements', null, InputOption::VALUE_REQUIRED, 'Database connection init statements', 'SET NAMES utf8')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Do not update database')
            ->addOption('start_id', null, InputOption::VALUE_REQUIRED, 'Start id for orders select')
            ->addOption('condition', null, InputOption::VALUE_REQUIRED, 'Condition for orders select')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit for orders select')
            ->addOption('ignore_hours', null, InputOption::VALUE_NONE, 'Ignore hours in created_at check. Use if due to different timezones on mysql servers time differs.')
            ->addOption('increment_id_suffix', null, InputOption::VALUE_REQUIRED, 'Add suffix to increment id of imported entities')
            ->addOption('test_time', null, InputOption::VALUE_NONE, 'Print created_at time of orders and exit.')
            ->addOption('list_tables', null, InputOption::VALUE_REQUIRED, 'List tables related to specified table')
            ->addOption('ignore_tables', null, InputOption::VALUE_REQUIRED, 'Comma separated list of tables to ignore in listing of related tables');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connectionSettings = $this->connectionSettingsFactory->create([
            'allowModifications' => true
        ]);

        if ($input->getOption('host')) {
            $connectionSettings->setHost($input->getOption('host'));
        }
        if ($input->getOption('port')) {
            $connectionSettings->setPort($input->getOption('port'));
        }
        if ($input->getOption('db')) {
            $connectionSettings->setDbname($input->getOption('db'));
        }
        if ($input->getOption('user')) {
            $connectionSettings->setUser($input->getOption('user'));
        }
        if ($input->getOption('password')) {
            $connectionSettings->setPassword($input->getOption('password'));
        }
        if ($input->getOption('init_statements')) {
            $connectionSettings->setInitStatements($input->getOption('init_statements'));
        }

        $processor = $this->orderImportProcessorFactory->create();
        $processor->setOutput($output);
        $processor->setConnectionSettings($connectionSettings);

        if ($input->getOption('list_tables')) {
            if ($input->getOption('ignore_tables')) {
                $ignore = explode(',', $input->getOption('ignore_tables'));
            } else {
                $ignore = [];
            }
            $processor->printRelatedTables($input->getOption('list_tables'), $ignore);
            return;
        }

        $processor->setTest($input->getOption('test'));
        if ($input->getOption('start_id')) {
            $processor->setStartId($input->getOption('start_id'));
        }
        if ($input->getOption('condition')) {
            $processor->setCondition($input->getOption('condition'));
        }
        if ($input->getOption('limit')) {
            $processor->setLimit($input->getOption('limit'));
        }
        if ($input->getOption('ignore_hours')) {
            $processor->setIgnoreDateHour(true);
        }
        if ($input->getOption('increment_id_suffix')) {
            $processor->setIncrementIdSuffix($input->getOption('increment_id_suffix'));
        }
        if ($input->getOption('test_time')) {
            $processor->testTime();
            return;
        }
        $processor->importOrders();
    }
}
