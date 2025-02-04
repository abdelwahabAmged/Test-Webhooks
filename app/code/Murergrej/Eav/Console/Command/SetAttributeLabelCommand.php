<?php

namespace Murergrej\Eav\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Api\Data\AttributeFrontendLabelInterfaceFactory;

class SetAttributeLabelCommand extends Command
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var AttributeFrontendLabelInterfaceFactory
     */
    protected $attributeFrontendLabelFactory;

    public function __construct(AttributeRepositoryInterface $attributeRepository, AttributeFrontendLabelInterfaceFactory $attributeFrontendLabelInterfaceFactory)
    {
        parent::__construct();
        $this->attributeRepository = $attributeRepository;
        $this->attributeFrontendLabelFactory = $attributeFrontendLabelInterfaceFactory;
    }

    protected function configure()
    {
        $this->setName('eav:attribute:set-label')
            ->addArgument('store_id', InputArgument::REQUIRED, 'Store Id')
            ->addArgument('entity_type', InputArgument::REQUIRED, 'Entity Type (customer, customer_address, catalog_category, catalog_product)')
            ->addArgument('code', InputArgument::REQUIRED, 'Attribute Code')
            ->addArgument('label', InputArgument::OPTIONAL, 'Attribute Frontend Label');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityType = $input->getArgument('entity_type');
        $code = $input->getArgument('code');
        $storeId = $input->getArgument('store_id');
        $label = $input->getArgument('label');

        $attribute = $this->attributeRepository->get($entityType, $code);
        $frontendLabels = $attribute->getFrontendLabels();

        $found = false;
        foreach ($frontendLabels as $key => $frontendLabel) {
            if ($frontendLabel->getStoreId() == $storeId) {
                if ($label != '') {
                    $frontendLabel->setLabel($label);
                } else {
                    unset($frontendLabel[$key]);
                }
                $found = true;
                break;
            }
        }
        if (!$found && $label != '') {
            $frontendLabels[] = $this->attributeFrontendLabelFactory->create()
                ->setStoreId($storeId)
                ->setLabel($label);
        }
        $attribute->setFrontendLabels($frontendLabels);
        $this->attributeRepository->save($attribute);
    }
}
