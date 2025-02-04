<?php

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateHomepageLinksBlock implements DataPatchInterface
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
     * CreateOfflineBlock constructor.
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
                'title' => 'Homepage links',
                'identifier' => 'homepage_links',
                'content' => '<div class="widget block block-static-block">
   <style>#html-body [data-pb-style=AFR770E],#html-body [data-pb-style=TCNJME9]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=AFR770E]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=TCNJME9]{align-self:stretch}#html-body [data-pb-style=FXKHL0C]{display:flex;width:100%}#html-body [data-pb-style=Y2297IK]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=Q949VQL]{display:inline-block}#html-body [data-pb-style=X7CBW37]{text-align:center}#html-body [data-pb-style=ACDE1M8]{display:inline-block}#html-body [data-pb-style=V9WXPTD]{text-align:center}#html-body [data-pb-style=C72K3O5]{display:inline-block}#html-body [data-pb-style=PTX3CX2]{text-align:center}#html-body [data-pb-style=A1KIBUM]{display:inline-block}#html-body [data-pb-style=UWEQXYW]{text-align:center}#html-body [data-pb-style=RU3U6FL]{display:inline-block}#html-body [data-pb-style=B8UI2IV]{text-align:center}</style>
   <div class="block-homepage-links" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="AFR770E">
      <h3 data-content-type="heading" data-appearance="default" data-element="main">Information</h3>
      <div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="TCNJME9">
         <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="FXKHL0C">
            <div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="Y2297IK">
               <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                  <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="Q949VQL">
                     <div class="pagebuilder-button-secondary" data-element="empty_link" data-pb-style="X7CBW37"><span data-element="link_text">FRAGT OG LEVERING</span></div>
                  </div>
               </div>
               <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                  <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="ACDE1M8">
                     <div class="pagebuilder-button-secondary" data-element="empty_link" data-pb-style="V9WXPTD"><span data-element="link_text">FAQ</span></div>
                  </div>
               </div>
               <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                  <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="C72K3O5">
                     <div class="pagebuilder-button-secondary" data-element="empty_link" data-pb-style="PTX3CX2"><span data-element="link_text">BETALINGSBETINGELSER</span></div>
                  </div>
               </div>
               <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                  <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="A1KIBUM">
                     <div class="pagebuilder-button-secondary" data-element="empty_link" data-pb-style="UWEQXYW"><span data-element="link_text">PRIVATLIVSPOLITIK</span></div>
                  </div>
               </div>
               <div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main">
                  <div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="RU3U6FL">
                     <div class="pagebuilder-button-secondary" data-element="empty_link" data-pb-style="B8UI2IV"><span data-element="link_text">HANDELSBETINGELSER</span></div>
                  </div>
               </div>
            </div>
         </div>
      </div>
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
