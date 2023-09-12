<?php
namespace Aha\Organization\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Organization extends Template {
    
    protected $userCount;
    
    protected $orgName;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    public function __construct(
       Context $context, 
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Model\OrganizationCustomer $orgCustomer,
        \Magento\Framework\App\Request\Http $request,
        \Aha\Tnc\Helper\Data $helper,
        \Aha\Organization\Helper\Data $orgHelper,
        \Aha\Organization\Model\OrgAdmin $orgAdmin,
        \Aha\Organization\Model\Organization $orgModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
       
        $this->customerSession = $customerSession;
        $this->orgCustomer = $orgCustomer;
        $this->request = $request;
        $this->_helper = $helper;
        $this->_orgHelper = $orgHelper;
        $this->orgAdmin = $orgAdmin;
        $this->orgModel = $orgModel;
        $this->scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }
    
    public function getOrgList(){
        $customerId = $this->customerSession->getId();
        $collection = $this->orgCustomer->getCollection();
        $collection->getSelect()
                ->join(
                    ['org' => $collection->getTable('aha_organization')],
                    'main_table.parent_id = org.entity_id',
                    ['org.org_name','org.po_request','org.credit_limit','org.forestna_id']
                )->where("main_table.customer_id = $customerId AND main_table.is_admin = 1 AND org.org_status = 1");
        
        return $collection;
    }
    
    public function newOrganizationUrl($param = null)
    {
       return $this->getUrl('organization/account/create', ['id' => $param]);
    }
    
    public function newUserUrl($param = null)
    {
        if(!$param){
            $param = $this->request->getParam('id');
        }
        
       return $this->getUrl('organization/account/createuser', ['id' => $param]);
    }
    
    public function saveUserUrl($param = null)
    {   
       return $this->getUrl('organization/account/saveuser');
    }
    
    public function getUserList() {
        $id = $this->request->getParam('id');
        
        $collection = $this->orgCustomer->getCollection();
        $collection->getSelect()
                ->join(
                    ['org' => $collection->getTable('aha_organization')],
                    'main_table.parent_id = org.entity_id',
                    ['org.org_name']
                )->join(
                    ['customer' => $collection->getTable('customer_entity')],
                    'customer.entity_id = main_table.customer_id',
                    ['customer.firstname','customer.lastname','customer.email']
                )->where("main_table.parent_id = $id AND main_table.is_admin = 1");
        
        return $collection;
    }
    
    public function getCustomerId() {
        return $this->customerSession->getId();
    }
    
    public function getUserCount($id) {
        if(isset($this->userCount[$id])){
            return $this->userCount[$id];
        }
        
        $collection = $this->orgCustomer->getCollection()
                ->addFieldToFilter('parent_id', $id)
                ->addFieldToFilter('is_admin', 1);
        
        $orgAdminCollection = $this->orgAdmin->getCollection()
                ->addFieldToFilter('tc_id', $id)
                ->addFieldToFilter('status', 0);
                
        return $this->userCount[$id] = $collection->getSize() + $orgAdminCollection->getSize();
    }
    
    public function getOrgAdminReq() {
        $id = $this->request->getParam('id');
        
        $collection = $this->orgAdmin->getCollection()
                ->addFieldToFilter('tc_id', $id)
                ->addFieldToFilter('status', 0);

        return $collection;
    }
    
    public function getOrgName($orgId = null) {
        if(!$orgId){
            $orgId = $this->request->getParam('id');
        }
        if(isset($this->orgName[$orgId])){
            return $this->orgName[$orgId];
        } else {
            $org = $this->orgModel->load($orgId);
            return $this->orgName[$orgId] = $org->getOrgName();
        }
    }
    
    public function getCurrentOrgId() {
        return $this->request->getParam('id');
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check apply invoice is allowed or not for this store;
     * @param \Aha\Organization\Model\Organization
     * @return boolean
     */
    public function isApplyInvoiceAllowed($organization)
    {
        $isAllowed = true;
        if ($this->_moduleManager->isEnabled('Aha_StoreSwitcher')) {
            if ($this->_storeManager->getStore()->getCode() ==
            \Aha\StoreSwitcher\Helper\Data::INTERNATIONAL_STOREVIEW_CODE &&
            empty($organization->getPoRequest())) {
                $isAllowed = false;
            }
        }
        return $isAllowed;
    }
}
