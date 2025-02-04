<?php
/**
 * @category    Murergrej
 * @package     Murergrej_PickupLocatorShippingMethodsFramework
 * @author      Ernests Verins <info@scandiweb.com>
 * @copyright   Copyright (c) 2025 Scandiweb, Inc (https://scandiweb.com)
 */

declare(strict_types=1);

namespace Murergrej\PickupLocatorShippingMethodsFramework\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\Carrier\GetPickupCarriersListInterface;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\Carrier\PickupCarrierCodesRegistrationPool;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\Carrier\PickupCarriers\AvailabilityProviderInterface;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\ConfigInterface;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\ResourceModel\TableRate\GetRateInterface;
use Magento\Framework\App\ResourceConnection;
use Vconnect\PickupLocatorShippingMethodsFramework\Model\Carrier\PickupLocatorComposite as SourcePickupLocatorComposite;

/**
 * Class PickupLocatorComposite
 */
class PickupLocatorComposite extends SourcePickupLocatorComposite
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param GetPickupCarriersListInterface $getPickupCarriersList
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param AvailabilityProviderInterface $carriersAvailabilityProvider
     * @param GetRateInterface $getRate
     * @param ConfigInterface $pickupLocatorConfig
     * @param PickupCarrierCodesRegistrationPool $registrationPool
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        GetPickupCarriersListInterface $getPickupCarriersList,
        protected ResultFactory $rateResultFactory,
        protected MethodFactory $rateMethodFactory,
        protected AvailabilityProviderInterface $carriersAvailabilityProvider,
        protected GetRateInterface $getRate,
        ConfigInterface $pickupLocatorConfig,
        PickupCarrierCodesRegistrationPool $registrationPool,
        protected ResourceConnection $resourceConnection,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $getPickupCarriersList,
            $rateResultFactory,
            $rateMethodFactory,
            $carriersAvailabilityProvider,
            $getRate,
            $pickupLocatorConfig,
            $registrationPool,
            $data
        );
    }

    /**
     * Validate if a PostNord rate matches the current request.
     *
     * @param array $postNordRate
     * @param RateRequest $request
     * @return bool
     */
    protected function isPostNordRateValid(array $postNordRate, RateRequest $request): bool
    {
        $postcode = (int)$request->getDestPostcode();
        $cartWeight = (float)$request->getPackageWeight();
        $cartTotal = (float)$request->getPackageValue();

        $zipFrom = (int)$postNordRate['dest_zip'];
        $zipTo = (int)$postNordRate['dest_zip_to'];
        $weightFrom = (float)$postNordRate['condition_from_value'];
        $weightTo = (float)$postNordRate['condition_to_value'];
        $orderTotal = (float)$postNordRate['order_total'];

        $isZipValid = $postcode >= $zipFrom && $postcode <= $zipTo;
        $isWeightValid = $postNordRate['condition_name'] === 'package_weight' &&
            $cartWeight >= $weightFrom &&
            $cartWeight <= $weightTo;
        $isOrderTotalValid = $cartTotal >= $orderTotal;

        return $isZipValid && $isWeightValid && $isOrderTotalValid;
    }

    /**
     * Fetch PostNord-specific rates from the webshopapps_matrixrate table.
     *
     * @param RateRequest $request
     * @return array
     */
    protected function getPostNordRates(RateRequest $request): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('webshopapps_matrixrate');

        $select = $connection->select()
            ->from($tableName)
            ->where('LOWER(shipping_method) LIKE ?', '%postnord%')
            ->where('website_id = ?', $request->getWebsiteId());

        return $connection->fetchAll($select);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     *
     * @return ?Result
     * @api
     */
    public function collectRates(RateRequest $request): ?Result
    {
        if (!$this->isActive()) {
            return null;
        }

        $result = $this->rateResultFactory->create();

        $carrierCode = $this->getCarrierCode();
        $carrierTitle = $this->getConfigData('title');

        // Get PostNord-specific rates from the webshopapps_matrixrate table
        $postNordRates = $this->getPostNordRates($request);

        foreach ($this->getCarriers() as $pickupCarrier) {
            if (!$this->carriersAvailabilityProvider->isAvailable($pickupCarrier, $request)) {
                continue;
            }

            $method = $this->rateMethodFactory->create();

            $method->setCarrier($carrierCode);
            $method->setCarrierTitle($carrierTitle);
            $method->setMethod($pickupCarrier->getCode());

            $rate = $this->getRate->execute($pickupCarrier, $request);

            // Adjust PostNord rates based on webshopapps_matrixrate data
            if (!empty($postNordRates)) {
                foreach ($postNordRates as $postNordRate) {
                    if (
                        $this->isPostNordRateValid($postNordRate, $request)
                    ) {
                        $method->setPrice($postNordRate['price']);
                        $method->setCost($postNordRate['price']);
                        $method->setMethodTitle($postNordRate['shipping_method']);
                        $result->append($method);
                        break; // Only apply the first matching PostNord rate
                    }
                }
            } elseif ($rate) {
                $shippingCost = $rate->getPrice();
                $method->setPrice($shippingCost);
                $method->setCost($shippingCost);
                $method->setMethodTitle($pickupCarrier->getMethodTitle());

                $result->append($method);
            }
        }

        return $result;
    }
}
