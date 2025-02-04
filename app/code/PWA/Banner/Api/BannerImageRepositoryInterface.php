<?php

namespace PWA\Banner\Api;

interface BannerImageRepositoryInterface
{
    /**
     * @param int|null $storeId
     * @return Data\BannerImageInterface[]
     */
    public function getImages($storeId = null);
}
