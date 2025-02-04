<?php

namespace Murergrej\Import\Console\Command;

use Magento\Framework\App\State;
use Murergrej\Import\Model\OrderImportProcessorFactory;
use Murergrej\Import\Model\Mysql\ConnectionSettingsFactory;
use Murergrej\Import\Model\ProductImageDuplicatesFixer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixProductImageDuplicatesCommand extends Command
{
    /**
     * @var ProductImageDuplicatesFixer
     */
    protected $productImageDuplicatesFixer;

    /**
     * @var State
     */
    protected $state;

    public function __construct(
        ProductImageDuplicatesFixer $productImageDuplicatesFixer,
        State $state
    ) {
        $this->productImageDuplicatesFixer = $productImageDuplicatesFixer;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('murergrej:import:fix-product-image-duplicates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->productImageDuplicatesFixer->setOutput($output);
        $this->productImageDuplicatesFixer->execute();
    }
}
