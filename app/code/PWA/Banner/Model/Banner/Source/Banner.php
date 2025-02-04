<?php

namespace PWA\Banner\Model\Banner\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Banner implements OptionSourceInterface
{
    /**
     * @var \PWA\Banner\Model\BannerImage
     */
    protected $banner;

    /**
     * Constructor
     *
     * @param \PWA\Banner\Model\BannerImage $banner
     */
    public function __construct(\PWA\Banner\Model\BannerImage $banner)
    {
        $this->banner = $banner;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->banner->getAvailableBanners();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
