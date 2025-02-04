<?php

namespace Murergrej\PalletShipping\Model\Carrier;

use Murergrej\PalletShipping\Model\CompositeWeightFactory;

class DSV extends AbstractCarrier
{
    /**
     * @var string
     */
    protected $_code = 'dsv';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory,
        CompositeWeightFactory $compositeWeightFactory,
        \Murergrej\PalletShipping\Model\ResourceModel\Carrier\DSV $carrierResource,
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
        return ['dsv' => $this->getConfigData('name')];
    }
}
