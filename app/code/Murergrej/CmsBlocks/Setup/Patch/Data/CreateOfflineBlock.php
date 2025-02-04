<?php

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;

/**
 * Class CreateOfflineBlock
 * 
 * This class is responsible for creating CMS blocks during the setup process.
 */
class CreateOfflineBlock implements DataPatchInterface
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
        // Set the area code to avoid errors in non-admin areas
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        // Define CMS blocks with title, identifier, and content
        $blocks = [
            [
                'title' => 'offline_store',
                'identifier' => 'offline_store',
                'content' =>'<style>#html-body [data-pb-style=A22D7L9],#html-body [data-pb-style=EL4IUMS]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=A22D7L9]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=EL4IUMS]{align-self:stretch}#html-body [data-pb-style=JO9MOQG]{display:flex;width:100%}#html-body [data-pb-style=DQUT2HC]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:41.6667%;align-self:stretch}#html-body [data-pb-style=YAIVOM7]{border-style:none}#html-body [data-pb-style=G0N7JR7],#html-body [data-pb-style=RSY8BVA]{max-width:100%;height:auto}#html-body [data-pb-style=S20GOOR]{width:100%;border-width:1px;border-color:#cecece;display:inline-block}#html-body [data-pb-style=D17X47G]{justify-content:flex-start;display:flex;flex-direction:column;width:58.3333%;align-self:stretch}#html-body [data-pb-style=D17X47G],#html-body [data-pb-style=DX7KNYP],#html-body [data-pb-style=HHKWEAL]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=HHKWEAL]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=DX7KNYP]{align-self:stretch}#html-body [data-pb-style=YXSFD1C]{display:flex;width:100%}#html-body [data-pb-style=C0WUPG6]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=WIXOA68]{border-style:none}#html-body [data-pb-style=AUY1M6W],#html-body [data-pb-style=F6TPKUS]{max-width:100%;height:auto}#html-body [data-pb-style=DXYL3NI]{justify-content:flex-start;display:flex;flex-direction:column;width:50%}#html-body [data-pb-style=DXYL3NI],#html-body [data-pb-style=WNH4HEY]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=CDKS4GX]{display:flex;width:100%}#html-body [data-pb-style=X6FPQX1]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=R81DPP7]{border-style:none}#html-body [data-pb-style=CEQA55P],#html-body [data-pb-style=EF77VVY]{max-width:100%;height:auto}#html-body [data-pb-style=A2UU6HX]{justify-content:flex-start;display:flex;flex-direction:column;width:50%}#html-body [data-pb-style=A2UU6HX],#html-body [data-pb-style=Q0R7J6F]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=R9P62EK]{display:flex;width:100%}#html-body [data-pb-style=TDG10FW]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=DKIOGH8]{border-style:none}#html-body [data-pb-style=LB5JHXO],#html-body [data-pb-style=TEMDTWB]{max-width:100%;height:auto}#html-body [data-pb-style=NWTYEY1]{justify-content:flex-start;display:flex;flex-direction:column;width:50%}#html-body [data-pb-style=NWTYEY1],#html-body [data-pb-style=VFWFAXD]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=DHCH9EC]{display:flex;width:100%}#html-body [data-pb-style=FNAMNAI]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=M2VDIBH]{border-style:none}#html-body [data-pb-style=G96TYS3],#html-body [data-pb-style=MHOE2KE]{max-width:100%;height:auto}#html-body [data-pb-style=H8R7E3C]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}@media only screen and (max-width: 768px) { #html-body [data-pb-style=DKIOGH8],#html-body [data-pb-style=M2VDIBH],#html-body [data-pb-style=R81DPP7],#html-body [data-pb-style=WIXOA68],#html-body [data-pb-style=YAIVOM7]{border-style:none} }</style><div data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="A22D7L9"><div class="pagebuilder-column-group first-row" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="EL4IUMS"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="JO9MOQG"><div class="pagebuilder-column first-part" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="DQUT2HC"><figure class="first-col mb-3" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="YAIVOM7"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/logo_1_.png}}" alt="" title="" data-element="desktop_image" data-pb-style="RSY8BVA"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/logo_1_.png}}" alt="" title="" data-element="mobile_image" data-pb-style="G0N7JR7"></figure><div class="font-extrabold first-col" data-content-type="text" data-appearance="default" data-element="main"><p id="XXY2LX6"><strong><span style="font-size: 24px;">Live, shop, socialize: come to our </span></strong><strong><span style="font-size: 24px;">store!</span></strong></p></div><div class="divsion" data-content-type="divider" data-appearance="default" data-element="main"><hr data-element="line" data-pb-style="S20GOOR"></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="D17X47G"><div data-content-type="html" data-appearance="default" data-element="main">&lt;iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d105127.24684935974!2d9.117070390521729!3d55.41770896603619!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x464b5bbbe40c98e9%3A0xc0b5e6f201701509!2sMurergrej.dk!5e0!3m2!1sen!2spl!4v1705424364857!5m2!1sen!2spl" class="w-full h-[480px]" height=480 style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"&gt;&lt;/iframe&gt;</div></div></div></div></div><div class="offline" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="HHKWEAL"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="DX7KNYP"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="YXSFD1C"><div class="pagebuilder-column adjust-width mr-3 md:mr-0" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="C0WUPG6"><figure class="max-md:!mr-[10px]" data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="WIXOA68"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/location_4.png}}" alt="" title="" data-element="desktop_image" data-pb-style="AUY1M6W"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/location_4.png}}" alt="" title="" data-element="mobile_image" data-pb-style="F6TPKUS"></figure></div><div class="pagebuilder-column second-col" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="DXYL3NI"><div class="underline font-extrabold controle_max_width text-[14px]" data-content-type="text" data-appearance="default" data-element="main"><p><span style="font-size: 14px; color: #3690be;"><span id="CFX5DIC">Murergrej.dk ApS - S�nderfenne 10, Jels DK-6630 </span>R�dding&nbsp;</span></p></div></div></div></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="WNH4HEY"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="CDKS4GX"><div class="pagebuilder-column adjust-width" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="X6FPQX1"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="R81DPP7"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/time_1.png}}" alt="" title="" data-element="desktop_image" data-pb-style="CEQA55P"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/time_1.png}}" alt="" title="" data-element="mobile_image" data-pb-style="EF77VVY"></figure></div><div class="pagebuilder-column second-col" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="A2UU6HX"><div class="font-extrabold text_wrap text-[14px]" data-content-type="text" data-appearance="default" data-element="main"><p><span style="color: #000000;">�bningstider: man-fre 8.00 - 17.00</span></p></div></div></div></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="Q0R7J6F"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="R9P62EK"><div class="pagebuilder-column adjust-width" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="TDG10FW"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="DKIOGH8"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/call_2.png}}" alt="" title="" data-element="desktop_image" data-pb-style="TEMDTWB"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/call_2.png}}" alt="" title="" data-element="mobile_image" data-pb-style="LB5JHXO"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="NWTYEY1"><div class="underline font-extrabold whitespace-nowrap text-[14px]" data-content-type="text" data-appearance="default" data-element="main"><p id="WUDB877"><span style="color: #3690be;">+45 7260 6630</span></p></div></div></div></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="VFWFAXD"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="DHCH9EC"><div class="pagebuilder-column adjust-width" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="FNAMNAI"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="M2VDIBH"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/mail_2.png}}" alt="" title="" data-element="desktop_image" data-pb-style="MHOE2KE"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/mail_2.png}}" alt="" title="" data-element="mobile_image" data-pb-style="G96TYS3"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="H8R7E3C"><div class="underline font-extrabold whitespace-nowrap text-[14px]" data-content-type="text" data-appearance="default" data-element="main"><p><span style="color: #3690be;">Info@murergrej.dk</span></p></div></div></div></div></div>',
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
