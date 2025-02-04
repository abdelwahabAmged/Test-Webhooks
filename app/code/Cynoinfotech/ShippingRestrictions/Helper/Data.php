<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_ShippingRestrictions
 */
namespace Cynoinfotech\ShippingRestrictions\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * Constructor
     * @param  \Magento\Checkout\Model\Session $checkoutSession
     * @return void
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $currentCustomer;
    /**
     * @var \Cynoinfotech\Shippingrestrict\Model\ShippingrestrictFactory
     */
    protected $_srModel;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_quoteSession;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $req;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var _errorMessages
     */
    protected $_errorMessages = [];
    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $validatorUtility;
    /**
     * @var \Cynoinfotech\Shippingrestrict\Model\ResourceModel\Shippingrestrict\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
	
	protected $serializerInterface;
	
    protected $_srCollection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $currentCustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $reqst,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\SalesRule\Model\Utility $utility,
        \Cynoinfotech\ShippingRestrictions\Model\ShippingrestrictionsFactory $shippingrestrictFactory,
        \Cynoinfotech\ShippingRestrictions\Model\ResourceModel\Shippingrestrictions\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->currentCustomer = $currentCustomer;
        $this->checkoutSession = $checkoutSession;
        $this->req = $reqst;
        $this->_quoteSession = $quoteSession;
        $this->quoteRepository = $quoteRepository;
        $this->validatorUtility = $utility;
        $this->_srModel = $shippingrestrictFactory;
        $this->_srCollection = $collectionFactory;
        $this->serializerInterface = $serializerInterface;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }
    
    /**
     * Functionality to get configuration values of plugin
     * @param $configPath: System xml config path
     * @return value of requested configuration
     */
     
    public function getConfig($configPath)
    {
        return $this->_scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function checkIfConditionsAreBlank($rule)
    {
        $unserializedConditions = [];
        $data = $rule->getData('conditions_serialized');
        $unserializedConditions = $this->serializerInterface->unserialize($data);
        if (is_array($unserializedConditions) && array_key_exists('conditions', $unserializedConditions)) {
            return false;
        }
        return true;
    }
    
    public function _applyCondition()
    {
        return $this->getConfig('cynoinfotech_shippingrestrictions/general/apply_conditions');
    }
    
    public function _applyCoupon()
    {
        return $this->getConfig('cynoinfotech_shippingrestrictions/general/apply_coupons');
    }
    
    public function _isEnableForAdmin()
    {
        return $this->getConfig('cynoinfotech_shippingrestrictions/general/restrict_for_admin');
    }
            
    public function getRestrictedShippingMethods()
    {
        $allStoreViews = [];
        $group_id = '';
        if ($this->req->getRouteName() == 'sales' && $this->req->getControllerName() == 'order_create') {
            if ($this->_isEnableForAdmin()) {
                $cartQuote=$this->quoteRepository->get($this->_quoteSession->getQuote()->getId());
                $group_id = $cartQuote->getCustomerGroupId();
                $currentStoreId = $cartQuote->getStoreId();
            } else {
                return [];
            }
        } else {
            $cartQuote=$this->checkoutSession->getQuote();
            if ($this->currentCustomer->isLoggedIn()) {
                $group_id = $this->currentCustomer->getCustomerGroupId();
            }
            $currentStoreId = $this->_storeManager->getStore()->getStoreId();
        }
        if (!$group_id || $group_id == '') {
            $group_id = 0;
        }
        $address = $cartQuote->getShippingAddress();
        $restrictedCarriers = [];
        $shippingRestrictionsCollection = $this->_srModel->create()->getCollection()
                                            ->setOrder('sort_order', 'DESC')->addFieldToFilter('status', '1');
        
        foreach ($shippingRestrictionsCollection as $shippingRestrictionRule) {
            $restrictCurrentRule = false;
            
            //-------------------------------------------//
            // check for Store
            $ruleAppliedForStores = explode(",", $shippingRestrictionRule->getStores());
            if (!in_array($currentStoreId, $ruleAppliedForStores) && !in_array(0, $ruleAppliedForStores)) {
		
                continue;
            }
            
            //-------------------------------------------//
            // check for days //
            
            $shippingtMethodAppliedDays = explode(",", $shippingRestrictionRule->getDays());
            $weekday = $this->dateTime->date('w');
            if (!in_array($weekday, $shippingtMethodAppliedDays)) {
                continue;
            }
            //-------------------------------------------//
            // check for Customer Group
            $ruleAppliedForGroups = explode(",", $shippingRestrictionRule->getCustomerGroup());
            if (!in_array($group_id, $ruleAppliedForGroups)) {
                continue;
            }else {
				$restrictCurrentRule = true;
			}
            
            //------------------------------------------//
            // check for Rule Conditions
			
			if($this->_applyCondition()){
			
				if ($shippingRestrictionRule && $this->checkIfConditionsAreBlank($shippingRestrictionRule)) {
					$restrictCurrentRule = true;
				}
				if (($this->_applyCondition() == 0) && !$this->checkIfConditionsAreBlank($shippingRestrictionRule)) {
					$restrictCurrentRule = false;
					continue;
				} elseif (!$this->validatorUtility->canProcessRule($shippingRestrictionRule, $address)) {
					$restrictCurrentRule = false;
					continue;
				} else {
					$restrictCurrentRule = true;
				}
			}
			
            //-------------------------------------------//
            // check for Coupon code
            $couponCodeArray = explode(",", $shippingRestrictionRule->getCouponcode());
			            
            if ($this->_applyCoupon() && !empty($couponCodeArray)) {
                if (in_array($cartQuote->getCouponCode(), $couponCodeArray)) {
                    $restrictCurrentRule = true;
                } else {
                    $restrictCurrentRule = false;
                    continue;
                }
            }             
			 
			if($restrictCurrentRule) {					
				foreach (explode(',', $shippingRestrictionRule->getCarriers()) as $c) {
					$restrictedCarriers[$c] = $shippingRestrictionRule->getErrorMsg();
				}				
			}
        }
		
        return (!empty($restrictedCarriers)) ? $restrictedCarriers:[];
    }

    public function getRestrictedShippingMethodsForMultishipping($address)
    {
        $allStoreViews = [];
        $group_id = '';
        if ($this->req->getRouteName() == 'sales' && $this->req->getControllerName() == 'order_create') {
            if ($this->_isEnableForAdmin()) {
                $cartQuote=$this->quoteRepository->get($this->_quoteSession->getQuote()->getId());
                $group_id = $cartQuote->getCustomerGroupId();
                $currentStoreId = $cartQuote->getStoreId();
            } else {
                return [];
            }
        } else {
            $cartQuote=$this->checkoutSession->getQuote();
            if ($this->currentCustomer->isLoggedIn()) {
                $group_id = $this->currentCustomer->getCustomerGroupId();
            }
            $currentStoreId = $this->_storeManager->getStore()->getStoreId();
        }
        if (!$group_id || $group_id == '') {
            $group_id = 0;
        }
		
       // $address = $cartQuote->getShippingAddress();		
		$address = $address;	
	   
        $restrictedCarriers = [];
        $shippingRestrictionsCollection = $this->_srModel->create()->getCollection()
                                            ->setOrder('sort_order', 'DESC')->addFieldToFilter('status', '1');
        
        foreach ($shippingRestrictionsCollection as $shippingRestrictionRule) {
            $restrictCurrentRule = false;
            
            //-------------------------------------------//
            // check for Store
            $ruleAppliedForStores = explode(",", $shippingRestrictionRule->getStores());
            if (!in_array($currentStoreId, $ruleAppliedForStores) && !in_array(0, $ruleAppliedForStores)) {
		
                continue;
            }
            
            //-------------------------------------------//
            // check for days //
            
            $shippingtMethodAppliedDays = explode(",", $shippingRestrictionRule->getDays());
            $weekday = $this->dateTime->date('w');
            if (!in_array($weekday, $shippingtMethodAppliedDays)) {
                continue;
            }
            //-------------------------------------------//
            // check for Customer Group
            $ruleAppliedForGroups = explode(",", $shippingRestrictionRule->getCustomerGroup());
            if (!in_array($group_id, $ruleAppliedForGroups)) {
                continue;
            }else {
				$restrictCurrentRule = true;
			}
            
            //------------------------------------------//
            // check for Rule Conditions
			
			if($this->_applyCondition()){
			
				if ($shippingRestrictionRule && $this->checkIfConditionsAreBlank($shippingRestrictionRule)) {
					$restrictCurrentRule = true;
				}
				if (($this->_applyCondition() == 0) && !$this->checkIfConditionsAreBlank($shippingRestrictionRule)) {
					$restrictCurrentRule = false;
					continue;
				} elseif (!$this->validatorUtility->canProcessRule($shippingRestrictionRule, $address)) {
					$restrictCurrentRule = false;
					continue;
				} else {
					$restrictCurrentRule = true;
				}
			}
			
            //-------------------------------------------//
            // check for Coupon code
            $couponCodeArray = explode(",", $shippingRestrictionRule->getCouponcode());
			            
            if ($this->_applyCoupon() && !empty($couponCodeArray)) {
                if (in_array($cartQuote->getCouponCode(), $couponCodeArray)) {
                    $restrictCurrentRule = true;
                } else {
                    $restrictCurrentRule = false;
                    continue;
                }
            }             
			 
			if($restrictCurrentRule) {				
				foreach (explode(',', $shippingRestrictionRule->getCarriers()) as $c) {
					$restrictedCarriers[$c] = $shippingRestrictionRule->getErrorMsg();
				}				
			}
        }
		
        return (!empty($restrictedCarriers)) ? $restrictedCarriers:[];
    }	
}
