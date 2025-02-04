<?php

/**
 * @category Murergrej
 * @package Murergrej_CmsBlock
 * @author Jorgena Shinjatari info@scandiweb.com
 * @copyright Copyright (c) 2024 Scandiweb, Inc (https://scandiweb.com)
*/

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;

/**
 * Class CreateReturnsBlock
 *
 * This class is responsible for creating CMS blocks during the setup process.
 */
class CreateReturnsBlock implements DataPatchInterface
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
     * CreateReturnsBlock constructor.
     *
     * @param BlockFactory $blockFactory
     * @param State $state
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        BlockFactory             $blockFactory,
        State                    $state,
        BlockRepositoryInterface $blockRepository
    )
    {
        $this->blockFactory = $blockFactory;
        $this->state = $state;
        $this->blockRepository = $blockRepository;
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
     * Apply method to create CMS blocks.
     *
     * @return $this
     */
    public function apply()
    {
        // Set the area code to avoid errors in non-admin areas
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        // Define CMS blocks with title, identifier, and content
        $blocks = [
            [
                'title' => 'Returns',
                'identifier' => 'returns',
                'content' => '
                    <style>#html-body [data-pb-style=DAE08KX]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:24px 0 0;padding:0}#html-body [data-pb-style=L76MBVC],#html-body [data-pb-style=TJ7QHLG],#html-body [data-pb-style=U452PIJ]{margin:0 0 8px;padding:0}#html-body [data-pb-style=XCWCHOF]{margin:0;padding:0}#html-body [data-pb-style=V490KEL]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:16px 0 0;padding:0}</style>
                    <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="DAE08KX">
                            <div class="add-hyphen" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="U452PIJ">
                                <p><span style="font-size: 14px;"><strong id="G7MRKG0">14-Day Right of Withdrawal:</strong> Customers have a 14-day window to withdraw from a purchase, starting from the day of receiving the item or the last item in a multi-item order.</span></p>
                            </div>
                            <div class="add-hyphen" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="TJ7QHLG">
                                <p><span style="font-size: 14px;"><strong id="G7MRKG0">Return Fees:</strong> Returns after 14 days incur fees - 20% within 30 days, 30% up to 3 months, and 40% for 3 to 6 months since ordering.</span></p>
                            </div>
                            <div class="add-hyphen" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="L76MBVC">
                                <p><span style="font-size: 14px;"><strong id="G7MRKG0">Buyer&#39;s Responsibility: </strong>Returns are at the buyer&#39;s expense and risk, requiring original, unbroken packaging.</span></p>
                            </div>
                            <div class="add-hyphen" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="XCWCHOF">
                                <p><span style="font-size: 14px;"><strong id="G7MRKG0">Return Procedure: </strong>Notify via email and return items within 14 days of cancellation. Use recommended carriers for traceability.</span></p>
                            </div>
                        </div>
                    </div>
                    <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="V490KEL">
                            <div data-content-type="html" data-appearance="default" data-element="main" data-decoded="true"><a href="" class="text-sm font-extrabold">Read full Return &amp; Right of Complaint information</a></div>
                        </div>
                    </div>
                ',
                'is_active' => 1,
                'stores' => [0], // 0 means all stores
            ],
            // Add more blocks here if necessary
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
     * Retrieve the list of aliases for this patch.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
