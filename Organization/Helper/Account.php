<?php
/**
 * Filename     : Data.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Helper;

use Aha\Organization\Model\OrganizationFactory;
use Aha\Organization\Model\OrganizationCustomerFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Aha\Organization\Model\TncAcceptance;
use Magento\Store\Model\StoreManagerInterface;
use Aha\Organization\Api\Data\TncAcceptanceInterfaceFactory;
use Aha\Organization\Model\TncAcceptanceRepository;
use Aha\Subscription\Helper\JourneyCheck;
use \Aha\Premier\Helper\Data as PremierData;

class Account extends AbstractHelper
{
    public function __construct(
        Context $context,
        OrganizationFactory $OrganizationFactory,
        OrganizationCustomerFactory $OrganizationCustomerFactory,
        \Aha\Organization\Model\Address $orgAddress,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Helper\Data $orgHelper,
        \Aha\Organization\Helper\Address $orgAddressHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Aha\Organization\Model\CdpCustomer $cdpCustomer,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Aha\Organization\Model\Customer $customCustomer,
        \Magento\Quote\Api\CartRepositoryInterface $quote,
        \Magento\Framework\App\ResourceConnection $dbresource,
        TncAcceptance $tncAcceptance,
        StoreManagerInterface $storeManager,
        TncAcceptanceInterfaceFactory $tncAcceptanceInterfaceFactory,
        TncAcceptanceRepository $tncAcceptanceRepo,
        JourneyCheck $journeyCheck,
        PremierData $premierData
    ) {
        $this->_OrganizationFactory = $OrganizationFactory->create();
        $this->_OrganizationCustomerFactory = $OrganizationCustomerFactory->create();
        $this->orgAddress = $orgAddress;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->orgHelper = $orgHelper;
        $this->orgAddressHelper = $orgAddressHelper;
        $this->_cart = $cart;
        $this->cartHelper = $cartHelper;
        $this->cdpCustomer = $cdpCustomer;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->customCustomer = $customCustomer;
        $this->quotemodel = $quote;
        $this->dbresource = $dbresource;
        $this->tncAcceptanceModal = $tncAcceptance;
        $this->storeManager = $storeManager;
        $this->tncAccIntFact = $tncAcceptanceInterfaceFactory;
        $this->tncAcceptanceRepo = $tncAcceptanceRepo;
        $this->journeyCheck = $journeyCheck;
        $this->premierData = $premierData;
        parent::__construct($context);
    }
    
    public function saveAddress($data)
    {
        $response = array();
        $model = $this->orgAddress;
        if(!empty($data['address_id']) && !$data['suborg']){
            $addressData = $this->orgAddress->load($data['address_id']);
            if($addressData->getId()){
                $model = $addressData;
                
            }
        }
        if(empty($model->getFirstname()) || empty($model->getLastname())){
            $model->setFirstname($data['org_name'])
                    ->setLastname($data['org_name']);
        }
        
        $street = implode("\n", $data['street']);
        
        if(isset($data['region_id'])){
            $model->setRegionId($data['region_id']);
        }

        $model->setTelephone($data['telephone'])
                ->setStreet($street)
                ->setCountryId($data['country_id'])
                ->setCity($data['city'])
                ->setRegion($data['region'])
                ->setPostcode($data['postcode'])
                ->setOrgId($data['tid'])
                ->setParentId($this->customerSession->getCustomerId());
        
        try{
            $model->save();
            $response['status'] = true;
            $response['address_id'] = $model->getId();
        } catch (\Exception $ex) {
            $response['status'] = false;
            $response['error'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function mapOrg($orgId, $CustomerId)
    {
        $collection = $this->_OrganizationCustomerFactory->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $CustomerId)
                ->getFirstItem();
        
        if($collection->hasData() && !$collection->getIsAdmin()){
            $collection->setIsAdmin(1)->save();
        } elseif(!$collection->hasData()) {
            $model = $this->_OrganizationCustomerFactory;
            $model->setParentId($orgId)
                    ->setCustomerId($CustomerId)
                    ->setIsAdmin(1);
            
            $model->save();
        } 
    }
    
    public function setOrganization($orgId, $customergroup = null)
    {
        $orgBillingEmail = '';
        $orgBillingEmailCc = '';
        if($orgId == "Myself"){
            $customergroup = '';
            $customer = $this->customCustomer->load($this->customerSession->getId());
            $memberTier = $customer->getMemberTier();
            $memberEndDate = $customer->getMemberEndDate();
            $this->customerSession->unsEtc();
            $storeCode = $this->storeManager->getStore()->getCode();
            $isPremierEnabled = $this->premierData->getConfig('pdh/setting/enabled');
            if ($isPremierEnabled && $storeCode == PremierData::INSIGHTS_STOREVIEW_CODE && $orgId == 'Myself' && !empty($memberTier) && date("Y-m-d") <= $memberEndDate) {
                $customergroup = $this->orgHelper->getPremierCustomerGroup($memberTier);
            }
            
            if(empty($customergroup)) {
                $customergroup = $this->orgHelper->getUserRoleID();
            }
            $this->customerSession->setOrgName('');
        } else {
            //if(empty($customergroup)){
                $orgData = $this->_OrganizationFactory->load($orgId);
                $customergroup = $orgData->getCustomerGroup();
                $this->customerSession->setOrgName($orgData->getOrgName());
                $this->customerSession->setLegalName($orgData->getLegalName());
                $this->customerSession->setEtc($orgData->getEtc());
                $orgBillingEmail = $orgData->getBillingEmail();
                $orgBillingEmailCc = $orgData->getBillingEmailCc();
            //}
        }
        $customerId = $this->customerSession->getId();
        $customer = $this->customer->load($customerId);

        if ($customer->getGroupId() != $customergroup) {
            $orgCustomer = $this->customCustomer->load($customerId);
            $orgCustomer->setGroupId($customergroup)->save();
        }

        try{
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('org_id',$orgId);
            $customer->updateData($customerData);
            $customerResource = $this->customerFactory->create();
            $customerResource->saveAttribute($customer, 'org_id');
        } catch (\Exception $ex) {
            throw new Exception("customer save: ". $ex->getMessage());
        }

        //Assign Customer Group to Session
        $this->customerSession->setCustomerGroupId($customergroup);
        $this->customerSession->setOrganization($orgId);
        $this->customerSession->setOrgBillingEmail($orgBillingEmail);
        $this->customerSession->setOrgBillingEmailCc($orgBillingEmailCc);
        $this->customerSession->setPincode('');
        //after changing customer group, reload the quote
        

        $this->reloadCart($customergroup,$orgId);
        
        return true;
    }
    
    public function setCDP($orgId, $postData)
    {
        $customerId = $this->customerSession->getId();
        $cdpCollection = $this->cdpCustomer->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('tc_id', $orgId)
                ->addFieldToFilter('cdp_id', $postData['cdp_id'])
                ->getFirstItem();
        
        if(!$cdpCollection->hasData()){
            $this->cdpCustomer->setTcId($orgId)
                    ->setCustomerId($customerId)
                    ->setCdpId($postData['cdp_id']);
            $this->cdpCustomer->save();
        }
        return true;
                
    }

    public function reloadCart($customergroup, $orgId)
    {
        $this->customerSession->setVerifyStore(true); // to verify store view once org switches
        if ($this->cartHelper->getItemsCount() > 0) 
        {
            /*
            * for insights store set default qty for myself journey
            */
            $this->journeyCheck->setQtyAsOneForMyself();
            
            /*
            * for insights store Reset the Quote Item SubStart SubEnd and TxnId
            */
            $this->journeyCheck->quoteItemSubscriptionReset();
            
            $customerId = $this->customerSession->getId();
            $customer = $this->customer->load($customerId);
        
            $quote = $this->quotemodel->get($this->_cart->getQuote()->getId());
            $quote->setCustomerGroupId($customergroup);
            $quote->setCustomerIsGuest(0);
            $this->quotemodel->save($quote);
            
            $this->_cart->getQuote()->collectTotals()->setTriggerRecollect(1);
            $this->_cart->save();
            if ($orgId != "" && $orgId != "Myself") 
            {
                $orgData = $this->_OrganizationFactory->load($orgId);
                $billing_address = $orgData->getDefaultBilling();
                $shipping_address = $orgData->getDefaultShipping();
            } else {  
                $billing_address = (!empty($customer->getData('default_billing'))) ? $customer->getData('default_billing') : null;
                $shipping_address = (!empty($customer->getData('default_shipping'))) ? $customer->getData('default_shipping') : null;
               
            }

            $quote_billing_address = $quote->getBillingAddress()->getData('customer_address_id');
            $quote_shipping_address = $quote->getShippingAddress()->getData('customer_address_id');
            $quote_addr_table = $this->dbresource->getTableName('quote_address');
            $conn = $this->dbresource->getConnection();
            if ($billing_address != $quote_billing_address)
            {
                $billing_address = (!empty($billing_address)) ? $billing_address : 'NULL';
                $sql = "Update " . $quote_addr_table . " set customer_address_id = $billing_address where quote_id =" . $this->_cart->getQuote()->getId() . " AND address_type ='billing'";
                $conn->query($sql);
            }

            if ($shipping_address != $quote_shipping_address) 
            {
                $shipping_address = (!empty($shipping_address)) ? $shipping_address : 'NULL';
                $sql = "Update " . $quote_addr_table . " set customer_address_id = $shipping_address where quote_id =" . $this->_cart->getQuote()->getId() . " AND address_type ='shipping'";
                $conn->query($sql);
            }
        }
    }

    public function saveMyselfTerms()
    {
        
        $customerId = $this->customerSession->getId();
        $customer = $this->customer->load($customerId);
//        $customer->setCustomAttribute('tnc_accepted', 1);
        
        try{
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('tnc_accepted',1);
            $customer->updateData($customerData);
            $customerResource = $this->customerFactory->create();
            $customerResource->saveAttribute($customer, 'tnc_accepted');

//            $this->customerRepositoryInterface->save($customer);
        } catch (\Exception $ex) {
            throw new Exception("customer save: ". $ex->getMessage());
        }
        return true;
    }
    
    public function saveOrganizationTerms()
    {
        $orgId = $this->customerSession->getOrganization();
        $orgData = $this->_OrganizationFactory->load($orgId);
        if($orgData->hasData()){
            $orgData->setTncStatus(1)
                    ->setTncAcceptedBy($this->customerSession->getId());

            try{
                $orgData->save();
            } catch (\Exception $ex) {
                throw new Exception("Organization terms save: " . $ex->getMessage());
            }
        }
        return true;
    }
    
    function saveTncAcceptance($orgId = null) {
        if (empty($orgId)) {
            $orgId = $this->customerSession->getOrganization();
        }
        $customerId = $this->customerSession->getCustomer()->getId();
        $store = $this->storeManager->getStore();
        $websiteId= $store->getWebsiteId();
        $groupId = $store->getGroupId();
        $storeId = $store->getId();

        $tncAccepData = $this->tncAccIntFact->create();
        if($orgId != 'Myself') {
            $tncAccepData->setOrgId($orgId);
        }

        $tncAccepData
                ->setCustomerId($customerId)
                ->setWebsiteId($websiteId)
                ->setGroupId($groupId)
                ->setStoreId($storeId);

        try{
            $this->tncAcceptanceRepo->save($tncAccepData);
        } catch (\Exception $ex) {
            throw new Exception("terms save: " . $ex->getMessage());
        }
        return true;
    }
}
