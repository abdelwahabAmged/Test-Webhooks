<?php

declare(strict_types=1);

namespace DV\ProfitMetrics\Observer;

use DV\ProfitMetrics\Helper\BotsUserAgentChecker;
use DV\ProfitMetrics\Model\ProfitHelper;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    public const PROFITMETRICS_COOKIE_TRACKING_DATA = 'pmTPTrack';
    public const COOKIE_CLICK_ID_URL_PARAM = 'gclid';
    public const FACEBOOK_CLICK_ID_URL_PARAM = 'fbclid';
    private const MAX_LENGTH = 100;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var \DV\ProfitMetrics\Model\VisitorFactory
     */
    private $visitorFactory;

    /**
     * @var BotsUserAgentChecker
     */
    private $botsUserAgentChecker;

    /**
     * @var \DV\ProfitMetrics\Model\ResourceModel\Visitor\CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Index constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \DV\ProfitMetrics\Helper\BotsUserAgentChecker $botsUserAgentChecker
     * @param \DV\ProfitMetrics\Model\VisitorFactory $visitorFactory
     * @param \DV\ProfitMetrics\Model\ResourceModel\Visitor\CollectionFactory $visitorCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \DV\ProfitMetrics\Helper\BotsUserAgentChecker $botsUserAgentChecker,
        \DV\ProfitMetrics\Model\VisitorFactory $visitorFactory,
        \DV\ProfitMetrics\Model\ResourceModel\Visitor\CollectionFactory $visitorCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->httpHeader = $httpHeader;
        $this->remoteAddress = $remoteAddress;
        $this->jsonSerializer = $jsonSerializer;
        $this->botsUserAgentChecker = $botsUserAgentChecker;
        $this->visitorFactory = $visitorFactory;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getEvent()->getData('request');

        if (!$request || !($request instanceof \Magento\Framework\App\Request\Http)) {
            $this->logger->error('Trying to handle controller_action_predispatch event without $request');

            return;
        }

        if ($this->botsUserAgentChecker->isBot()) {
            return;
        }

        $this->setRequest($request);
        $trackingData = $this->getTrackingDataFromRequest();
        $visitorId = $this->customerSession->getData(ProfitHelper::PROFITMETRICS_VISITOR_ID_SESSION_KEY);
        $lastUpdateTimestamp = $this->customerSession->getData(ProfitHelper::PROFITMETRICS_UPDATE_TIMESTAMP_SESSION_KEY);

        if ($visitorId && $trackingData['timestamp'] === $lastUpdateTimestamp) {
            return;
        }

        $visitor = $this->visitorFactory->create();

        if ($visitorId) {
            /** @var \DV\ProfitMetrics\Model\ResourceModel\Visitor\Collection $visitorsCollection */
            $visitorsCollection = $this->visitorCollectionFactory->create();
            $visitorsCollection->addFieldToFilter('entity_id', $visitorId);
            /** @var \DV\ProfitMetrics\Model\Visitor $visitor */
            $visitor = $visitorsCollection->getFirstItem();
        }

        try {
            if ($trackingData['timestamp'] > $visitor->getTimestamp()) {

                $trackingData = array_filter($trackingData, static function ($value) {
                    return (string) $value !== '';
                });

                if (
                    isset($trackingData['fbc'])
                    && $this->facebookClickIdIsExpired($trackingData['fbc'], $visitor->getFbc())
                ) {
                    unset($trackingData['fbc']);
                }

                $visitor->addData($trackingData);
                $visitor->save();
            }

            $this->customerSession->setData(ProfitHelper::PROFITMETRICS_VISITOR_ID_SESSION_KEY, $visitor->getId());
            $this->customerSession->setData(ProfitHelper::PROFITMETRICS_UPDATE_TIMESTAMP_SESSION_KEY, $visitor->getTimestamp());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }

    /**
     * @return array
     */
    public function getTrackingDataFromRequest(): array
    {
        $profitMetricsTrackingCookieString = $this->getRequest()->getCookie(
            self::PROFITMETRICS_COOKIE_TRACKING_DATA,
            ''
        );
        $profitMetricsCookieData = [];


        if ($profitMetricsTrackingCookieString) {
            try {
                $profitMetricsCookieData = $this->jsonSerializer->unserialize($profitMetricsTrackingCookieString);
            } catch (\Exception $exception) {
                if (!$profitMetricsTrackingCookieString) {
                    $this->logger->error($exception->getMessage(), [
                        'trace' => $exception->getTraceAsString()
                    ]);
                }

                $profitMetricsCookieData = [];
            }
        }

        $timestamp = $this->getArrayParameter('timestamp', $profitMetricsCookieData);
        $currentTimestamp = time();
        $googleClickId = (string) $this->getRequest()->getParam(self::COOKIE_CLICK_ID_URL_PARAM);

        if (strlen($googleClickId) > self::MAX_LENGTH) {
            $googleClickId = substr($googleClickId, 0, self::MAX_LENGTH);
        }

        if ($googleClickId) {
            $timestamp = $currentTimestamp;
        }

        $facebookClickIdFromUrl = (string) $this->getRequest()->getParam(self::FACEBOOK_CLICK_ID_URL_PARAM);
        $facebookClickIdFromProfitMetrics = (string) $this->getArrayParameter('fbc', $profitMetricsCookieData);
        $facebookClickId = $facebookClickIdFromProfitMetrics;

        if ($facebookClickIdFromUrl) {
            $facebookClickId = 'fb.1.' . $currentTimestamp * 1000 . '.' . $facebookClickIdFromUrl;
            $timestamp = $currentTimestamp;
        }


        if (!$facebookClickId && $facebookClickIdFromProfitMetrics) {
            $facebookClickId = $facebookClickIdFromProfitMetrics;
        }

        if (strlen($facebookClickId) > self::MAX_LENGTH) {
            $facebookClickId = substr($facebookClickId, 0, self::MAX_LENGTH);
        }

        $visitorData = $this->getVisitorData();

        if (!$timestamp || $visitorData) {
            $timestamp = $currentTimestamp;
        }

        return [
            'gacid' => $this->getArrayParameter('gacid', $profitMetricsCookieData),
            'gacid_source' => $this->getArrayParameter('gacid_source', $profitMetricsCookieData),
            'gclid' => $googleClickId,
            'fbp' => $this->getArrayParameter('fbp', $profitMetricsCookieData),
            'fbc' => $facebookClickId,
            'cua' => $this->httpHeader->getHttpUserAgent(),
            'cip' => $this->remoteAddress->getRemoteAddress(),
            't' => $visitorData,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * @param $parameter
     * @param array $parameters
     * @return string
     */
    private function getArrayParameter($parameter, array $parameters): string
    {
        return (string) ($parameters[$parameter] ?? '');
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    private function getRequest(): \Magento\Framework\App\Request\Http
    {
        return $this->request;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return ControllerActionPredispatch
     */
    private function setRequest(\Magento\Framework\App\Request\Http $request): ControllerActionPredispatch
    {
        $this->request = $request;

        return $this;
    }

    private function getVisitorData()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $utmSource = (string) $request->getParam('utm_source');
        $utmCampaign = (string) $request->getParam('utm_campaign');
        $utmMedium = (string) $request->getParam('utm_medium');
        $visitorData = [];
        $visitorDataJson = '';

        if (strlen($utmSource) > self::MAX_LENGTH) {
            $utmSource = substr($utmSource, 0, self::MAX_LENGTH);
        }

        if (strlen($utmCampaign) > self::MAX_LENGTH) {
            $utmCampaign = substr($utmCampaign, 0, self::MAX_LENGTH);
        }

        if (strlen($utmMedium) > self::MAX_LENGTH) {
            $utmMedium = substr($utmMedium, 0, self::MAX_LENGTH);
        }

        if ($utmSource && $utmCampaign && $utmMedium) {
            $visitorData = [
                'utm_source' => $utmSource,
                'utm_campaign' => $utmCampaign,
                'utm_medium' => $utmMedium,
            ];
        }

        if ($visitorData) {
            try {
                $visitorDataJson = $this->jsonSerializer->serialize($visitorData);
            } catch (\InvalidArgumentException $exception) {
                $this->logger->error($exception->getMessage(), [
                    'trace' => $exception->getTraceAsString()
                ]);
            }
        }

        return $visitorDataJson;
    }

    /**
     * @param $newFacebookClickId
     * @param $savedFacebookClickId
     * @return bool
     */
    private function facebookClickIdIsExpired($newFacebookClickId, $savedFacebookClickId): bool
    {
        if (!$savedFacebookClickId || !$newFacebookClickId) {
            return false;
        }

        $newFacebookClickIdTimestamp = $this->getFacebookClickTimestamp($newFacebookClickId);
        $savedFacebookClickIdTimestamp = $this->getFacebookClickTimestamp($savedFacebookClickId);

        return $newFacebookClickIdTimestamp <= $savedFacebookClickIdTimestamp;
    }

    /**
     * @param string $facebookClickId
     * @return int
     */
    private function getFacebookClickTimestamp(string $facebookClickId): int
    {
        $components = explode('.', $facebookClickId);

        return isset($components[2]) ? (int) $components[2] : 0;
    }
}
