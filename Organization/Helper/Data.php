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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Aha\Organization\Model\TncAcceptance;
use Aha\Organization\Model\TncAcceptanceRepository;

class Data extends AbstractHelper
{
    const ORG_TC_NAME = 'TC';

    const ORG_NON_TC_NAME = 'Non TC';
    
    const ORG_TC_ID = 1;

    const ORG_NON_TC_ID = 2;
    
    const ALLOWED_ROLE = array('TCA', 'TCC');

    const PRIORITY_ROLE = array('TCC', 'TCA', 'INS');

    const INVOICING_STATUS = array(
        0 => 'Not Applied',
        1 => 'Approved',
        2 => 'Pending',
        3 => 'Declined',
        4 => 'On Hold'
    );
    const FOREST_CUSTOMER_CCL ='forest/customer/ccl';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;
    
    protected $_OrganizationFactory;
    protected $_OrganizationCustomerFactory;
    
    /**
     * @var Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * 
     * @param Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OrganizationFactory $OrganizationFactory
     * @param OrganizationCustomerFactory $OrganizationCustomerFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Group $group,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Aha\Tnc\Model\SubscribersList $subscriberListModel,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrganizationFactory $OrganizationFactory,
        OrganizationCustomerFactory $OrganizationCustomerFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterface $addressDataFactory,
        \Aha\Organization\Model\Address $orgAddress,
        \Magento\Framework\UrlInterface $urlBuilder, 
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Model\Cdp $cdpModel,
        \Aha\Organization\Model\CdpCustomer $cdpCustomerModel,
        TncAcceptance $tncAcceptance,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TncAcceptanceRepository $tncAcceptanceRepository
    ) {
        $this->backendUrl = $backendUrl;
        $this->scopeConfig = $scopeConfig;
        $this->group = $group;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->subscriberListModel = $subscriberListModel;
        $this->storeManager = $storeManager;
        $this->_OrganizationFactory = $OrganizationFactory->create();
        $this->_OrganizationCustomerFactory = $OrganizationCustomerFactory->create();
        $this->_resource = $resource;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->orgAddress = $orgAddress;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->cdpModel = $cdpModel;
        $this->cdpCustomerModel = $cdpCustomerModel;
        $this->tncAcceptance = $tncAcceptance;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->tncAcceptanceRepository = $tncAcceptanceRepository;
        parent::__construct($context);
    }
        
    /**
     * Return current store Id
     *
     * @return Int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
    
    /**
     * Return current store Group Id
     *
     * @return Int
     */
    public function getGroupId()
    {
        return $this->storeManager->getStore()->getGroupId();
    }
    
    /**
     * Return current Website Id
     *
     * @return Int
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    public function getCustomersGridUrl()
    {
        return $this->backendUrl->getUrl('organization/customer/index', ['_current' => true]);
    }
    
    public function getUIFormUrl(){
        return $this->backendUrl->getUrl('trainingorganization/trainingcenter/edit', ['_current' => true]);
    }
    
    public function MapCustomer($data, $orgModel) 
    {
        if(isset($data['customers'])){
            $orgCustomerCollection = $this->getCollectionByOrgId($orgModel->getId());
            $customerIds = explode('&', $data['customers']);
            $existingCustomer = array();

            foreach ($orgCustomerCollection as $orgCustomer){
                if(!in_array($orgCustomer->getCustomerId(), $customerIds) && $orgCustomer->getIsAdmin()){
                    // delete unmap customer
                    $orgCustomer->delete();
                } elseif(in_array($orgCustomer->getCustomerId(), $customerIds) && !$orgCustomer->getIsAdmin()) {
                    //set existing customer as admin
                    $orgCustomer->setIsAdmin(1)->save();
                }
                $existingCustomer[] = $orgCustomer->getCustomerId();
            }
            //assign the new customer mapping
            foreach ($customerIds as $id){
                if((int)$id && !in_array($id, $existingCustomer)){
                    $model = $this->_OrganizationCustomerFactory;
                    $model->setParentId($orgModel->getId())
                            ->setCustomerId($id)
                            ->setIsAdmin(1);
                    $model->save();
                    $model->unsetData();
                }
            }
        }
        
    }
    
    public function MapOrganization($data) 
    {
        $customerId = $data['customer']['entity_id'];
        $customerOrgCollection = $this->getCollectionByCustomerId($customerId);
        $orgIds = explode('&', $data['organization']);
        $existingOrg = array();

        foreach ($customerOrgCollection as $customerOrg){
            if(!in_array($customerOrg->getParentId(), $orgIds) && $customerOrg->getIsAdmin()){
                // delete unmap customer
                $customerOrg->delete();
            } elseif(in_array($customerOrg->getParentId(), $orgIds) && !$customerOrg->getIsAdmin()) {
                //set existing customer as admin
                $customerOrg->setIsAdmin(1)->save();
            }
            $existingOrg[] = $customerOrg->getParentId();
        }
        foreach ($orgIds as $id){
            if((int)$id && !in_array($id, $existingOrg)){
                $model = $this->_OrganizationCustomerFactory;
                $model->setParentId($id)
                        ->setCustomerId($customerId)
                        ->setIsAdmin(1);
                $model->save();
                $model->unsetData();
            }
        }
    }
    
    public function getCollectionByOrgId($orgId) {
        return $this->_OrganizationCustomerFactory->getCollection()
                    ->addFieldToFilter('parent_id', $orgId);
    }
    
    public function getCollectionByCustomerId($customerId) {
        return $this->_OrganizationCustomerFactory->getCollection()
                    ->addFieldToFilter('customer_id', $customerId);
    }
    
    public function unAssignCustomers($orgCustomerCollection) {
        foreach ($orgCustomerCollection as $orgCustomer){
            $orgCustomer->delete();
        }
    }
    
    public function SaveAddress($data, $orgModel) {
        if(isset($data['address'])){
            $this->deleteAddress($data, $orgModel->getId());
            $orgDefaultAddress['default_billing'] = null;
            $orgDefaultAddress['default_shipping'] = null;
            foreach ($data['address'] as $key => $address) {
                if((int)$key){
                    $model = $this->orgAddress->load($key);
                } else {
                    $model = $this->orgAddress;
                }
                $address['street'] = implode("\n", $address['street']);
                try {
                    
                    $model->setData($address);
                    $model->setOrgId($orgModel->getId());
                    $model->save();
                    
                    
                    if($address['default_billing'] == 'true'){
                        $orgDefaultAddress['default_billing'] = $model->getId();
                    }
                    if($address['default_shipping'] == 'true'){
                        $orgDefaultAddress['default_shipping'] = $model->getId();
                    }
                } catch (\Exception $exc) {
                    $exc->getTraceAsString();
                }
            }
            $this->setDefaultFlag($orgModel, $orgDefaultAddress);
        }
    }
    
    public function setDefaultFlag($model, $addressFlag) {
        try{
            $model->setDefaultShipping($addressFlag['default_shipping'])
                ->setDefaultBilling($addressFlag['default_billing'])
                ->save();
        } catch (\Exception $ex) {
            $ex->getMessage();
        }
        
    }
    
    public function deleteAddress($addressData, $orgId) {
        $addressCollection = $this->orgAddress
                ->getCollection()
                ->addFieldToFilter('org_id', $orgId);
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('quote_address');

        if(isset($addressData['address'])){
            $modifiedAddress = array_keys($addressData['address']);
            foreach ($addressCollection as $address) {
                if($address->getId() && !in_array($address->getId(), $modifiedAddress)){
                    $sql = "Update " . $tableName . " Set customer_address_id = NULL where customer_address_id = " . $address->getId();
                    $connection->query($sql);
                    $address->delete();
                }
            }
        } else {
            foreach ($addressCollection as $address) {
                $sql = "Update " . $tableName . " Set customer_address_id = NULL where customer_address_id = " . $address->getId();
                $connection->query($sql);
                $address->delete();
            }
        }
    }
    
    
    /*
     * Added For TNC module
     * Returns Customer Collection Mapped to Organization
     * 
     */
   
    public function getOrgCustomerCollection(){
         return $this->_OrganizationCustomerFactory->getCollection();
    }
    
    /*
     * Get Org & customer mapping for viewing & printing the order
     */
    
    public function getCustomerOrgMapping($orgId, $customerId) {
        $collection = $this->_OrganizationCustomerFactory->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_admin', 1);
        if ($collection->getSize()) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Added For TNC module
     * Returns Organization Collection 
     * 
     */
    
    public function getOrgCollection(){
       return $this->_OrganizationFactory->getCollection();
                
    }
          
    /*
     * Added For TNC module
     * Updates the Organization Tnc Status 
     * 
     */
    
    public function SaveOrgTnc($orgId,$customerId)
    {
           $OrgModel = $this->_OrganizationFactory;

            $postUpdate = $OrgModel->load($orgId);
            $postUpdate->settnc_status(1);
            $postUpdate->settnc_accepted_by($customerId);

            if ($postUpdate->save()) {
                return $postUpdate->getData();
            } else {
                return false;
            }
    }
  
    /*
     * Added For TNC module
     * Return Org Data based ont he Org Id
     * 
     */
    
    public function GetOrgData($orgId)
    {
        $OrgModel = $this->_OrganizationFactory;

        $postUpdate = $OrgModel->load($orgId);
        
        return $postUpdate->getData();
         
    }
    /*
     * Added For TNC module
     * Return Collection with Customer Organization Mapping
     * 
     */
    
    public function getCustomerOrganization($customerId){
        $collection = $this->getOrgCustomerCollection();
        
        $tableName = $this->_resource->getTableName('aha_organization');
        $collection->getSelect()->join(
                ['org' => $tableName], 
                'org.entity_id = main_table.parent_id', 
                ['org.org_name']
        )->where("main_table.customer_id = $customerId AND org_type = 1");
              
        
       return $collection;
    }
    
    public function getOrgListByCustomer($customerId) {
        $collection = $this->getOrgCustomerCollection();
        $collection->getSelect()
                ->join(
                    ['org' => $collection->getTable('aha_organization')],
                    'main_table.parent_id = org.entity_id',
                    ['org.org_name','org.tc_id_number']
                )->where("main_table.customer_id = $customerId AND org.org_status = 1");
        return $collection;
    }

    public function getOrgWithParent($customerId)
    {
        $collection = $this->getOrgCustomerCollection();
        $collection->getSelect()
                ->join(
                    ['org' => $collection->getTable('aha_organization')],
                    'main_table.parent_id = org.entity_id',
                    ['org.org_name','org.tc_id_number','org.sub_org']
                )->where("main_table.customer_id = $customerId AND org.org_status = 1 AND main_table.is_admin = 1")
                ->group("org.entity_id")
                ->order('org.org_name');
        return $collection;
    }
    
    public function getOrgByTCID($tcId) {
        $org = $this->_OrganizationFactory->load($tcId, 'tc_id');
        if($org->hasData()){
            return $org;
        } else {
            return false;
        }
    }
    
    public function getAccountCreateUrl($param = null)
    {
        return $this->urlBuilder->getUrl('organization/account/create', ['_secure' => true, 'id' => $param]);
    }
    
    public function validateOrgAdmin($orgId, $customerId) {
        $collection = $this->_OrganizationCustomerFactory->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_admin', 1);
        if($collection->getSize()){
            return true;
        } else {
            return false;
        }
    }
    
    public function validateCustomerOrgAdmin($orgId, $customerId) {
        
        if ( filter_var($orgId, FILTER_VALIDATE_INT) === false  || filter_var($customerId, FILTER_VALIDATE_INT) === false) {
            return false;
        } 
        
        if (empty($orgId)|| empty($customerId) ) {
           return false;
        }
        
        $collection = $this->_OrganizationCustomerFactory->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_admin', 1)
                ->getFirstItem();
        if($collection->hasData()){
            return true;
        } else {
            return false;
        }
    }
    
    public function GetOrgById($orgId)
    {
        $OrgModel = $this->_OrganizationFactory;
        return $OrgModel->load($orgId);
    }
    
    public function getOrgAdminList($id = null)
    {
        if(!$id){
            $id = $this->customerSession->getTempOrgId();
        }
        $collection = $this->_OrganizationCustomerFactory->getCollection();
        $collection->getSelect()
                ->join(
                    ['customer' => $collection->getTable('customer_entity')],
                    'customer.entity_id = main_table.customer_id',
                    ['customer.firstname','customer.lastname','customer.email']
                )->where("main_table.parent_id = $id AND main_table.is_admin = 1");
        
        return $collection;
    }
    
    /*
     * Return Customer Group 
    */
    
    public function getUserRoleID() {
        
        $roleId = null;
        $role = $this->customerSession->getCustomerData()->getCustomAttribute('role')->getValue();
        
        if (!empty($role)) {
            $role = explode(',',$role);
            
            if(count($role) > 1){
                foreach (Data::PRIORITY_ROLE as $value){
                    if(in_array($value, $role)){
                        $roleId = $this->getCustomerGroupIdByCode($value);
                        break;
                    } else {
                        $roleId = $this->scopeConfig->getValue('customer/create_account/default_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    }
                }
            } else {
                $roleId = $this->getCustomerGroupIdByCode($role[0]);
            }
        } else {
            //return default customer role
            $roleId = $this->scopeConfig->getValue('customer/create_account/default_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $roleId;
    }
    
    public function getCustomerGroupIdByCode($code)
    {
        if($code == "INS"){
            $code = "INSTRUCTOR";
        }
        return $this->group->load($code, 'customer_group_code')->getId();
    }

    public function getTermsPopup($orgId = null)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $websiteId = $this->getWebsiteId();
        $groupId = $this->getGroupId();
        $response['terms'] = false;
        $response['subscription'] = false;
        
        if (isset($customerId)) {
            $subscription = $this->subscriberListModel->getCollection()
                    ->addFieldToFilter('customer_id' , $customerId)
                    ->addFieldToFilter('subscriber_website_id' , $websiteId)
                    ->addFieldToFilter('group_id' , $groupId);

            if ($subscription->getSize()) {
                $response['subscription'] = false;
            } else {
                $response['subscription'] = true;
            }

            if (empty($orgId)) {
                $orgId = $this->customerSession->getOrganization();
            }

            if ($orgId == "Myself") {
                $criteria = $this->searchCriteriaBuilder
                        ->addFilter('website_id', $websiteId, 'eq')
                        ->addFilter('group_id', $groupId, 'eq')
                        ->addFilter('customer_id', $customerId, 'eq')
                        ->addFilter('org_id', true, 'null')
                        ->create();

                $items = $this->tncAcceptanceRepository->getList($criteria)->getItems();
                if(!$items){
                    $response['terms'] = true;
                }
            } else {
                $orgData = $this->GetOrgById($orgId);

                if($orgData->getTncRequired()) {
                    $criteria = $this->searchCriteriaBuilder
                            ->addFilter('website_id', $websiteId, 'eq')
                            ->addFilter('group_id', $groupId, 'eq')
                            ->addFilter('org_id', $orgId, 'eq')
                            ->create();

                    $items = $this->tncAcceptanceRepository->getList($criteria)->getItems();
                    if(!$items){
                        $response['terms'] = true;
                    }
                }
            }
        }
        return $response;
    }
    /*
     * $location 0 from Popup
     * $location 1 from Customer Account Dasboard
     */
    
    public function saveSubsctiption($value = 0,$location = 0)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $websiteId = $this->getWebsiteId();
        $groupId = $this->getGroupId();
        
        $subscriptionData = $this->subscriberListModel->getCollection()
                ->addFieldToFilter('customer_id' , $customerId)
                ->addFieldToFilter('subscriber_website_id' , $websiteId)
                ->addFieldToFilter('group_id' , $groupId)
                ->getFirstItem();
        
        if($subscriptionData->hasData()){
            if($location == 0){
                return true;
            }
            if($subscriptionData->getSubscriberStatus() != $value){
                $subscriptionData->setSubscriberStatus($value)->save();
            }
        } else {
            $model = $this->subscriberListModel;
            $model->setCustomerId($customerId)
                    ->setSubscriberEmail($this->customerSession->getCustomer()->getEmail())
                    ->setSubscriptionType(1)
                    ->setSubscriberStatus($value)
                    ->setGroupId($groupId)
                    ->setSubscriberWebsiteId($websiteId)
                    ->setStoreId($this->storeManager->getStore()->getId());
            
            try{
                $model->save();
            } catch (\Exception $ex) {
                throw new Exception("Subscription Save : " . $ex->getMessage());
            }
        }
        
        return true;
    }
    
    public function isAddressExist($addressId){
        if($this->orgAddress->load($addressId)->getId()){
            return true;
        } else {
            return false;
        }
        
    }
    
    public function getCDPList() {
        $customerId = $this->customerSession->getId();
        $groupId = $this->getGroupId();
        $uid = $this->customerSession->getCustomerData()->getCustomAttribute('uid')->getValue();
        $collection = $this->cdpModel->getCollection();
        $collection->getSelect()
                ->joinLeft(
                    ['cdpcustomer' => $collection->getTable('aha_org_cdp_customer')],
                    'main_table.entity_id = cdpcustomer.cdp_id',
                    ['cdpcustomer.entity_id as cdpentity']
                )->where("cdpcustomer.customer_id = $customerId AND main_table.status =1 AND main_table.group_id = $groupId  OR FIND_IN_SET('$uid',main_table.admin)")
                ->group(array("main_table.entity_id",'cdpcustomer.entity_id'))->order('cdpcustomer.entity_id DESC');

        return $collection;
    }
    
    public function getCDPListByTcId($tcId) {
        if (!empty($tcId)) {
            $collection = $this->cdpCustomerModel->getCollection()
                    ->addFieldToFilter('tc_id', $tcId);

            return $collection;
        } else {
            return false;
        }
    }
    
    public function getAssociatedOrgCount(){
        $orgCollection = $this->getCollectionByCustomerId($this->customerSession->getId());
        $count = 0;
        
        $orgCollection->addFieldToFilter('is_admin', 1);
        
        if($orgCollection->getSize() > 0){
            $count = $orgCollection->getSize();
        }
        
        return $count;
    }
    /**
     * gets all active stores for admin add / edit org page.
     * 
     */
    public function getStores() {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = array();  
        $options[0] = 'None';
        foreach ($storeManagerDataList as $key => $value) { 
          if(!$value['is_active'])
          {
              continue; 
          }
          $options[$key] =  $value['name'];          
        }
        return $options;
    }
    /**
     * Get default credit limit
     */
    public function getDefaultCreditLimit()
    {
        return $this->scopeConfig->getValue( self::FOREST_CUSTOMER_CCL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $this->storeManager->getStore()->getStoreId());
    }
    
    public function getPremierCustomerGroup ($code) {
        // const vars or code format
        return $this->group->load(trim($code), 'customer_group_code')->getId();
    } 
}
