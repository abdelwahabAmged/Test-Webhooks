<?php

namespace Murergrej\Import\Console\Command;

use Murergrej\Import\Model\CustomerAddressFixerFactory;
use Murergrej\Import\Model\Mysql\ConnectionSettingsFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixCustomerAddressesCommand extends Command
{
    /**
     * @var CustomerAddressFixerFactory
     */
    protected $customerAddressFixerFactory;

    /**
     * @var ConnectionSettingsFactory
     */
    protected $connectionSettingsFactory;

    public function __construct(
        CustomerAddressFixerFactory $customerAddressFixerFactory,
        ConnectionSettingsFactory $connectionSettingsFactory
    ) {
        $this->customerAddressFixerFactory = $customerAddressFixerFactory;
        $this->connectionSettingsFactory = $connectionSettingsFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('murergrej:import:fix-customer-addresses')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Do not update database')
            ->addOption('start_id', null, InputOption::VALUE_REQUIRED, 'Start id for orders select')
            ->addOption('condition', null, InputOption::VALUE_REQUIRED, 'Condition for orders select')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit for orders select')
            ->addOption('list_invalid_billing', null, InputOption::VALUE_NONE, 'List customers with invalid default billing addresses')
            ->addOption('list_invalid_shipping', null, InputOption::VALUE_NONE, 'List customers with invalid default shipping addresses')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixer = $this->customerAddressFixerFactory->create();
        $continue = true;
        foreach ([
            'list_invalid_billing' => 'billing',
            'list_invalid_shipping' => 'shipping'
                 ] as $option => $addressType) {
            if ($input->getOption($option)) {
                $result = $fixer->getCustomersWithInvalidDefaultAddresses($addressType);
                $output->writeln('entity_id email default_billing default_shipping');
                foreach ($result as $customer) {
                    $output->writeln($customer['entity_id'] . ' ' . $customer['email']. ' ' . ($customer['default_billing'] ?? 'NULL') . ' ' . ($customer['default_shipping'] ?? 'NULL'));
                }
                $output->writeln('Total: ' . count($result));
                $continue = false;
            }
        }
        if (!$continue) {
            return;
        }

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

        $fixer->setOutput($output);
        $fixer->setConnectionSettings($connectionSettings);

        $fixer->setTest($input->getOption('test'));
        if ($input->getOption('start_id')) {
            $fixer->setStartId($input->getOption('start_id'));
        }
        if ($input->getOption('condition')) {
            $fixer->setCondition($input->getOption('condition'));
        }
        if ($input->getOption('limit')) {
            $fixer->setLimit($input->getOption('limit'));
        }

        $fixer->execute();
    }
}
