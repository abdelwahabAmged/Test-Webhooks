<?php

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;

/**
 * Class TrustpilotPDP    
 * 
 * This class is responsible for creating CMS blocks during the setup process.
 */
class TrustpilotPDP implements DataPatchInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * CreateCmsBlocks constructor.
     * 
     * @param BlockFactory $blockFactory
     * @param State $state
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        BlockFactory $blockFactory,
        State $state,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->state = $state;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Apply method to create CMS blocks.
     * 
     * @return $this
     */
    public function apply()
    {
        // Set the area code to avoid errors
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        // Define your CMS blocks here with exact titles and identifiers, content is empty
        $blocks = [
            [
                'title' => 'trust_piolt_PDP',
                'identifier' => 'trust_piolt_PDP',
                'content' => '<div data-content-type="html" data-appearance="default" data-element="main">{{block class="Magento\Framework\View\Element\Template" template="Magento_Catalog::product/view/trustpiolt_pdp.phtml"}}</div>',
                'is_active' => 1,
                'stores' => [0], // 0 means all stores
            ],
        ];

        foreach ($blocks as $data) {
            $block = $this->blockFactory->create()->load($data['identifier'], 'identifier');
            if (!$block->getId()) {
                $block->setData($data);
                $this->blockRepository->save($block);
            }
        }

        return $this;
    }

    /**
     * Retrieve the list of dependencies for this patch.
     * 
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Retrieve the list of aliases for this patch.
     * 
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
