<?php

namespace PWA\FrontendRedirect\Model;

interface RouterInterface
{
    public function match(\Magento\Framework\App\RequestInterface $request);
}
