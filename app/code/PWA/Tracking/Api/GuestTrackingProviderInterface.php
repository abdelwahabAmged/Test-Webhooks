<?php

namespace PWA\Tracking\Api;

interface GuestTrackingProviderInterface
{
    /**
     * @param string $hash
     * @return mixed
     */
    public function getByHash($hash);
}
