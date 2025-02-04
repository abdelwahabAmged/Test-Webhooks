<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Helper;

class BotsUserAgentChecker
{
    /**
     * @var string[]
     */
    private $restrictedUserAgents = [
        'AdsBot-Google',
        'AdsBot-Google-Mobile',
        'Pinterestbot',
        'AhrefsSiteAudit',
        'AhrefsBot',
        'Seekport Crawler',
        'DuckDuckBot',
        'PingdomPageSpeed',
        'pingbot',
        'Googlebot',
        'Storebot-Google',
        'YandexAccessibilityBot',
        'adidxbot',
        'bingbot',
        'SMTBot',
        'HubSpot Crawler',
        'e.ventures Investment Crawler',
        'Cincraw',
        'Facebot',
        'Twitterbot',
        'Jooblebot',
        'YisouSpider',
        'YandexMetrika',
        'Applebot',
        'PagePeeker',
        'Linespider',
        'proximic',
        'Algolia Crawler Renderscript',
        'PetalBot',
        'SEOFeedback_WebCrawler',
        'oBot',
        'Impact Radius Compliance Bot',
        'Cocolyzebot',
        'nlnbot',
        'SemrushBot-SA',
        'Bytespider',
        'RyteBot',
        'BrandVeritySpider',
        'ethical-bugbot',
        'Screaming Frog SEO Spider',
        'BublupBot',
        'bitlybot',
        'Better Uptime Bot',
        'KargoBot-Artemis-Mobile',
        'YandexVideoParser',
        'DotBot',
        'BLEXBot',
        'CheckMarkNetwork'
    ];

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    public function __construct(
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        $this->httpHeader = $httpHeader;
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public function isBot($userAgent = ''): bool
    {
        $userAgent = $userAgent ?: $this->httpHeader->getHttpUserAgent();

        foreach ($this->restrictedUserAgents as $restrictedUserAgent) {
            if (stripos($userAgent, $restrictedUserAgent) !== false) {
                return true;
            }
        }

        return false;
    }
}
