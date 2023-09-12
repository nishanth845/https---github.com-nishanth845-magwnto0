<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/

namespace Aha\Organization\Block\Account;

class Address extends \Magento\Customer\Block\Address\Edit
{
    protected $_orgAddress = null;
    /**
     * @var \Aha\InternationalTelephoneInput\Block\PhoneNumber
     */
    public $intPhoneNumber;

    public function __construct(
        \Aha\Organization\Helper\Data $orgHelper,
        \Magento\Framework\View\Element\Template\Context $context, 
        \Magento\Directory\Helper\Data $directoryHelper, 
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Aha\InternationalTelephoneInput\Block\PhoneNumber $intPhoneNumber,
        \Magento\Framework\App\Cache\Type\Config $configCacheType, 
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory, 
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, 
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, 
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory, 
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer, 
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, array $data = array() 
        //\Magento\Customer\Model\AttributeChecker $attributeChecker = null
    ) {
        $this->_orgHelper = $orgHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context, 
            $directoryHelper, 
            $jsonEncoder, 
            $configCacheType, 
            $regionCollectionFactory, 
            $countryCollectionFactory, 
            $customerSession,
            $addressRepository, 
            $addressDataFactory, 
            $currentCustomer, 
            $dataObjectHelper, 
            $data
            //$attributeChecker
        );
        $this->intPhoneNumber = $intPhoneNumber;
    }
    public function getParam($param) {
        
    }
    
//    public function getOrgData($tcId){
//        return $this->_orgHelper->getOrgByTCID($tcId);
//    }
    
    public function getOrgAddressID($addressId = null)
    {
        return $addressId;
    }
    
    public function getRegionId()
    {
        return $this->region_id;
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $orgData = $this->getOrgData();
        $orgTempData = $this->customerSession->getOrgData();
        $this->region_id = 0;
        if($orgData){
            if(isset($orgTempData['telephone'])){
                $this->_address->setTelephone($orgTempData['telephone'])
                        ->setStreet($orgTempData['street'])
                        ->setCity($orgTempData['city'])
                        ->setPostcode($orgTempData['postcode'])
                        ->setCountryId($orgTempData['country_id']);
                (isset($orgTempData['region_id'])) ? $this->region_id = $orgTempData['region_id'] : 0;
            } elseif ($orgData && $orgData->getDefaultBilling()) {
                if($this->_orgHelper->isAddressExist($orgData->getDefaultBilling())){
                    $this->_address = $this->_addressRepository->getById($orgData->getDefaultBilling());
                    $this->region_id = $this->_address->getRegionId();
                }
            }
        }

    }
    
    public function getOrgData(){
        $id = $this->_customerSession->getTempOrgId();
        if($id && $this->isAllowed()){
            return $this->_orgHelper->GetOrgById($id);
        } else {
            return false;
        }
    }

    public function getPhoneConfig(){
        return $this->intPhoneNumber->phoneConfig();
    }

    public function isAllowed()
    {
        $result = false;
        if($this->customerSession->isLoggedIn()){
            if ($this->customerSession->getCustomerData()->getCustomAttribute('role')->getValue()) {
                $role = explode(',', $this->customerSession->getCustomerData()->getCustomAttribute('role')->getValue());
                if(array_intersect($role, $this->_orgHelper::ALLOWED_ROLE)){
                    $result = true;
                }
            }
        }
        return $result;
    }
}
