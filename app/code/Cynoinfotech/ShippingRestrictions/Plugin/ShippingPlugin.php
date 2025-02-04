<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Plugin;

use Magento\Quote\Model\Quote\Address as QuoteAddress;

class ShippingPlugin
{
	 public function __construct(
		\Psr\Log\LoggerInterface $logger,
        \Cynoinfotech\ShippingRestrictions\Helper\Data $helper		
    ) {
		$this->logger = $logger;
        $this->helper = $helper;
    }

	public function afterGetShippingRates($subject, $result, $address)
	{	
		$groups = $result;		

		if ($this->helper->getConfig('cynoinfotech_shippingrestrictions/general/active')){
		
			$displayError = $this->helper->getConfig('cynoinfotech_shippingrestrictions/general/display_error');			
			$restrictedRules = $this->helper->getRestrictedShippingMethodsForMultishipping($address);			
					
			foreach ($groups as $codeg => $_rates) {
			
				foreach ($_rates as $code => $rate){
					
					if ($displayError) {
					
						if (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
						
							$eM = $restrictedRules[$rate->getCarrier().'_'. $rate->getMethod()];						
							$rate->setMethod('NULL');
							$rate->setErrorMessage($eM);
							$rate->setCode($rate->getCarrier().'_error');
							$rate->setMethodDescription('NULL');
							$rate->setMethodTitle('NULL');	
						
						} elseif (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
							unset($groups[$codeg]);						
						}				
					
					} elseif (in_array($rate->getCarrier().'_'. $rate->getMethod(), array_keys($restrictedRules))) {
						unset($groups[$codeg]);					
					}
					
				}
			}
		}	
		
		return $groups;
	}	
		
}
