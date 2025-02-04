<?php

namespace Murergrej\Eav\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterfaceFactory;

class GetAttributeLabelCommand extends Command
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        parent::__construct();
        $this->attributeRepository = $attributeRepository;
    }

    protected function configure()
    {
        $this->setName('eav:attribute:get-label')
            ->addArgument('entity_type', InputArgument::REQUIRED, 'Entity Type (customer, customer_address, catalog_category, catalog_product)')
            ->addArgument('code', InputArgument::REQUIRED, 'Attribute Code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityType = $input->getArgument('entity_type');
        $code = $input->getArgument('code');

        $attribute = $this->attributeRepository->get($entityType, $code);
        $frontendLabels = $attribute->getFrontendLabels();

        $output->writeln('default ' . $attribute->getDefaultFrontendLabel());
        foreach ($frontendLabels as $key => $frontendLabel) {
            $output->writeln($frontendLabel->getStoreId() . ' ' . $frontendLabel->getLabel());
        }
    }
}
