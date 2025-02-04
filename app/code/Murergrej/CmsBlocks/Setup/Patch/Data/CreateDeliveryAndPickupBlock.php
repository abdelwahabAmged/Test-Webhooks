<?php

namespace Murergrej\CmsBlocks\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\State;
use Magento\Cms\Api\BlockRepositoryInterface;

class CreateDeliveryAndPickupBlock implements DataPatchInterface
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
                'title' => 'Delivery & pickup options',
                'identifier' => 'delivery_and_pickup',
                'content' => '
                    <style>#html-body [data-pb-style=OIYAOTJ]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0}#html-body [data-pb-style=DFGHEPT]{margin:0;padding:16px 0 0}#html-body [data-pb-style=FUP9R3N]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:1px;margin:0;padding:0}#html-body [data-pb-style=SN7VAW0]{margin:0;padding:12px 0}#html-body [data-pb-style=FWTP6QT]{width:100%;border-color:#e3e3e3;display:inline-block}#html-body [data-pb-style=X85BCBK]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 8px;align-self:stretch}#html-body [data-pb-style=WQR5GPW]{display:flex;width:100%}#html-body [data-pb-style=EEI6MI6],#html-body [data-pb-style=SRIQUOF]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.66666667%;align-self:stretch}#html-body [data-pb-style=SRIQUOF]{width:33.33333333%}#html-body [data-pb-style=MGY0Q1V]{text-align:right}#html-body [data-pb-style=DFKWOCW]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:1px;margin:0;padding:0}#html-body [data-pb-style=DWKC7EP]{margin:0;padding:12px 0}#html-body [data-pb-style=F1DFJ5I]{width:100%;border-color:#e3e3e3;display:inline-block}#html-body [data-pb-style=XTRWXYI]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 8px;align-self:stretch}#html-body [data-pb-style=EA4UR8D]{display:flex;width:100%}#html-body [data-pb-style=COWL1AM],#html-body [data-pb-style=OUSVF7E]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.66666667%;align-self:stretch}#html-body [data-pb-style=OUSVF7E]{width:33.33333333%}#html-body [data-pb-style=D9VSAQ8]{text-align:right}#html-body [data-pb-style=IYONS9B]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:1px;margin:0;padding:0}#html-body [data-pb-style=R9SFOJ9]{margin:0;padding:12px 0}#html-body [data-pb-style=WJKWYJD]{width:100%;border-color:#e3e3e3;display:inline-block}#html-body [data-pb-style=JFC1H4V]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 8px;align-self:stretch}#html-body [data-pb-style=KROFQBM]{display:flex;width:100%}#html-body [data-pb-style=N2L5H7F],#html-body [data-pb-style=UJG71L8]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.66666667%;align-self:stretch}#html-body [data-pb-style=UJG71L8]{width:33.33333333%}#html-body [data-pb-style=LE2AQKI]{text-align:right}#html-body [data-pb-style=VLWI21B]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:1px;margin:0;padding:0}#html-body [data-pb-style=B4213VC]{margin:0;padding:12px 0}#html-body [data-pb-style=RVVYMEK]{width:100%;border-color:#e3e3e3;display:inline-block}#html-body [data-pb-style=M124Q3A]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 8px;align-self:stretch}#html-body [data-pb-style=RUS6N7W]{display:flex;width:100%}#html-body [data-pb-style=M9KEWMQ],#html-body [data-pb-style=SVSCPRE]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.66666667%;align-self:stretch}#html-body [data-pb-style=SVSCPRE]{width:33.33333333%}#html-body [data-pb-style=QEKG1RI]{text-align:right}#html-body [data-pb-style=MWMKVW6]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;min-height:1px;margin:0;padding:0}#html-body [data-pb-style=BACU8S7]{margin:0;padding:12px 0}#html-body [data-pb-style=V86CIHH]{width:100%;border-color:#e3e3e3;display:inline-block}#html-body [data-pb-style=WR7DD92]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 8px;align-self:stretch}#html-body [data-pb-style=O2PRUPU]{display:flex;width:100%}#html-body [data-pb-style=GMU4I1B],#html-body [data-pb-style=M5UQB5N]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:66.66666667%;align-self:stretch}#html-body [data-pb-style=M5UQB5N]{width:33.33333333%}#html-body [data-pb-style=DKS2N4J]{text-align:right}</style>
                    <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="OIYAOTJ">
                            <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="DFGHEPT">
                                <p style="line-height: 24px;"><span style="font-size: 16px;">Free shipping for orders over 2.500 kr</span></p>
                            </div>
                        </div>
                        </div>
                        <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="FUP9R3N">
                            <div data-content-type="divider" data-appearance="default" data-element="main" data-pb-style="SN7VAW0">
                                <hr data-element="line" data-pb-style="FWTP6QT">
                            </div>
                            <div class="pagebuilder-column-group two-columns-flex" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="X85BCBK">
                                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="WQR5GPW">
                                    <div class="pagebuilder-column first-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="EEI6MI6">
                                    <div class="font-bold" data-content-type="text" data-appearance="default" data-element="main">
                                        <p style="line-height: 24px;"><span style="color: #1d1f22;"><span id="CYXQKVW" style="font-size: 16px;">Pick it up yourself today&nbsp;</span></span></p>
                                    </div>
                                    </div>
                                    <div class="pagebuilder-column second-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="SRIQUOF">
                                    <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="MGY0Q1V">
                                        <p><span style="color: #0d9344; font-size: 16px;">FREE</span></p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="font-normal text-sm" data-content-type="text" data-appearance="default" data-element="main">
                                <p style="line-height: 20px;">S&oslash;nderfenne 10, Jels, 6630 R&oslash;dding.</p>
                            </div>
                        </div>
                        </div>
                        <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="DFKWOCW">
                            <div data-content-type="divider" data-appearance="default" data-element="main" data-pb-style="DWKC7EP">
                                <hr data-element="line" data-pb-style="F1DFJ5I">
                            </div>
                            <div class="pagebuilder-column-group two-columns-flex" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="XTRWXYI">
                                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="EA4UR8D">
                                    <div class="pagebuilder-column first-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="COWL1AM">
                                    <div class="font-bold" data-content-type="text" data-appearance="default" data-element="main">
                                        <p style="line-height: 24px;"><span style="color: #1d1f22;"><span id="JR3YS0Y" style="font-size: 16px;">Post shop delivery</span></span></p>
                                    </div>
                                    </div>
                                    <div class="pagebuilder-column second-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="OUSVF7E">
                                    <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="D9VSAQ8">
                                        <p><span style="color: #1d1f22; font-size: 16px;">from 29,-</span></p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="font-normal text-sm" data-content-type="text" data-appearance="default" data-element="main">
                                <p id="E7CN4G3" style="line-height: 20px;"><span style="color: #1d1f22;">1 - 3 days</span></p>
                            </div>
                        </div>
                        </div>
                        <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="IYONS9B">
                            <div data-content-type="divider" data-appearance="default" data-element="main" data-pb-style="R9SFOJ9">
                                <hr data-element="line" data-pb-style="WJKWYJD">
                            </div>
                            <div class="pagebuilder-column-group two-columns-flex" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="JFC1H4V">
                                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="KROFQBM">
                                    <div class="pagebuilder-column first-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="N2L5H7F">
                                    <div class="font-bold" data-content-type="text" data-appearance="default" data-element="main">
                                        <p id="J9WYHNH" style="line-height: 24px;"><span style="color: #1d1f22;"><span style="font-size: 16px;">Courrier delivery</span></span></p>
                                    </div>
                                    </div>
                                    <div class="pagebuilder-column second-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="UJG71L8">
                                    <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="LE2AQKI">
                                        <p><span style="color: #1d1f22; font-size: 16px;">from 39,-</span></p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="font-normal text-sm" data-content-type="text" data-appearance="default" data-element="main">
                                <p id="E7CN4G3" style="line-height: 20px;"><span style="color: #1d1f22;">Denmark - 1-3 days, Norway - 2-5 days, Sweden - 2-3 days</span></p>
                            </div>
                        </div>
                        </div>
                        <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="VLWI21B">
                            <div data-content-type="divider" data-appearance="default" data-element="main" data-pb-style="B4213VC">
                                <hr data-element="line" data-pb-style="RVVYMEK">
                            </div>
                            <div class="pagebuilder-column-group two-columns-flex" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="M124Q3A">
                                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="RUS6N7W">
                                    <div class="pagebuilder-column first-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="M9KEWMQ">
                                    <div class="font-bold" data-content-type="text" data-appearance="default" data-element="main">
                                        <p id="KX7HAWJ" style="line-height: 24px;"><span style="color: #1d1f22;"><span style="font-size: 16px;">Brink delivery</span></span></p>
                                    </div>
                                    </div>
                                    <div class="pagebuilder-column second-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="SVSCPRE">
                                    <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="QEKG1RI">
                                        <p><span style="color: #1d1f22; font-size: 16px;">from 49,-</span></p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="font-normal text-sm" data-content-type="text" data-appearance="default" data-element="main">
                                <p id="E7CN4G3" style="line-height: 20px;"><span style="color: #1d1f22;">Samme dag levering til Sydjylland &amp; Fyn ved bestilling inden kl. 9. Se g&aelig;ldende zone her</span></p>
                            </div>
                        </div>
                        </div>
                        <div data-content-type="row" data-appearance="contained" data-element="main">
                        <div class="delivery-info-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="MWMKVW6">
                            <div data-content-type="divider" data-appearance="default" data-element="main" data-pb-style="BACU8S7">
                                <hr data-element="line" data-pb-style="V86CIHH">
                            </div>
                            <div class="pagebuilder-column-group two-columns-flex" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="WR7DD92">
                                <div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="O2PRUPU">
                                    <div class="pagebuilder-column first-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="GMU4I1B">
                                    <div class="font-bold" data-content-type="text" data-appearance="default" data-element="main">
                                        <p style="line-height: 24px;"><span style="color: #1d1f22;"><span id="WUXJD6T" style="font-size: 16px;">Brink night delivery</span></span></p>
                                    </div>
                                    </div>
                                    <div class="pagebuilder-column second-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="M5UQB5N">
                                    <div class="font-extrabold" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="DKS2N4J">
                                        <p><span style="color: #1d1f22; font-size: 16px;">from 219,-</span></p>
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
