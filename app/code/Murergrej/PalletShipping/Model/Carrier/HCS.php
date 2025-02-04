<?php

namespace Murergrej\PalletShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Murergrej\PalletShipping\Model\CompositeWeightFactory;

class HCS extends AbstractCarrier
{
    /**
     * @var string
     */
    protected $_code = 'hcs';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory,
        CompositeWeightFactory $compositeWeightFactory,
        \Murergrej\PalletShipping\Model\ResourceModel\Carrier\HCS $carrierResource,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $resultMethodFactory, $compositeWeightFactory, $carrierResource, $data);
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['hcs' => $this->getConfigData('name')];
    }

    protected function getRateResultMethod($rate, RateRequest $request, $freeQty)
    {
        $method = parent::getRateResultMethod($rate, $request, $freeQty);

        if ($request->getPalletQty() <= 15) {
            $truckCost = $this->getConfigData('truck_cost_to15');
        } else {
            $truckCost = $this->getConfigData('truck_cost_from16');
        }
        $method->setCost($method->getCost() + $truckCost);

        return $method;
    }
}
