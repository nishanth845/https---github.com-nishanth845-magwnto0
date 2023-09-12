<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/

namespace Aha\Organization\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Punchout\Gateway\Helper\GatewayEnv;
use \Magento\Store\Model\StoreManagerInterface;

class Popup extends Template
{
    /**
     * @var GatewayEnv
     */
    private $gatewayEnv;

    public function __construct(
       Context $context, 
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Aha\Tnc\Helper\Data $helper,
        \Aha\Organization\Helper\Data $orgHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aha\Organization\Model\OrgAdmin $orgAdmin,
        GatewayEnv $gatewayEnv,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
       
        $this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->messageManager = $messageManager;
        $this->_helper = $helper;
        $this->_orgHelper = $orgHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->orgAdmin = $orgAdmin;
        $this->gatewayEnv = $gatewayEnv;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * check logged-in user is PunchOut User
     * @return string
     */
    public function getIsPunchoutLogin()
    {
        return $this->gatewayEnv->isPunchoutSession();
    }

    public function orgPopupUrl()
    {
       return $this->getUrl('organization/index/index', ['_secure' => true]);
    }

    /*
     * To check Whether the Customer Is mapped to any Organization or not
     * else will Assign to Myself
     * 
     * 0=> will Display Organization select Popup to User
     * 1=> Will Display only Terms & condition popup if required based on Organization or User
     * 
     */
    public function Orgpopup()
    {
        $orgpopup =  $this->Customer_attribute('org_id');
      
        if($orgpopup == ""){
            /*
             * Check Is Customer mapped to any Organization
             */
            $customerId = $this->customerSession->getCustomer()->getId();
            if(isset($customerId)){
                $data = $this->_helper->OrganizationList($customerId);
                if (empty($data)) {
                    return 1;  
                }else{
                    return 0;
                }
            }
        }else{
            $this->customerSession->setOrganization($orgpopup);
            return 1;
        }
    }
   
    /*
     * To check Whether the Customer Need to Go through the Terms Check
     * 0=> will Display Terms &condition  Popup to User
     * 1=> Will Not dislay Popup
     * 
     */
    
    public function TermspopupReq()
    {
        $customerId= $this->customerSession->getCustomer()->getId();
        
        /*
         * Check from Session if Terms Poup is required
         */
        if($this->customerSession->getOrgHeaderTerms()){
            return 0;
        }
        
        if(isset($customerId)){
            $orgpopup =  $this->Customer_attribute('org_id');
            if($orgpopup == ""){
                return 0;
            }else{
                if($orgpopup == "Myself"){
                    $termsreq =  $this->Customer_attribute('tnc_accepted');
                    return $termsreq;
                }else{
                    $res = $this->_helper->OrgTermsReq($orgpopup);

                    if($res['tnc_required'] == 1){
                        if($res['tnc_status'] == 0){
                            $termspopupreq = 0;
                        }else{
                            $termspopupreq = 1;
                        }
                    }else{
                            /*Check if Subscription is required for the User*/
                        if($this->_helper->Subscribed($customerId) == ""){
                             $termspopupreq = 0;
                        }else{
                             $termspopupreq = 1;
                        }
                    }


                        return $termspopupreq;
                    }
            }
            
        }else{
            return 1;
        }
    }
    
    /*
     * Return organization List mapped to the Customer
     * else will Assign to Myself
     * 
     * return bool
     */
    
    public function OrgList()
    {
       $customerId = $this->customerSession->getCustomer()->getId();
       $data = array();
       if(isset($customerId)){
            $data = $this->_helper->OrganizationList($customerId);
            return $data;
       }else{
           return $data;
       }
    }

    /*
     * To check Whether the Customer has accepted the terms for himself or not
     * 
     * return bool
     */
    
    public function termsaccepted()
    {
        $termsreq = $this->Customer_attribute('tnc_accepted');
        
        return $termsreq;
    }
    
    /*
     * To check Whether the Customer has Subscribed the Mailing List
     * 
     * return bool
     */
    
    public function IsSubscribed()
    {
       $customerId = $this->customerSession->getCustomer()->getId();
       
       return  $this->_helper->Subscribed($customerId);
    }
    
    /*
     * To check Whether the Customer has cancle from the Orgniazation selection Popup
     * 
     */
    
    public function IsCancleOrgPopup()
    {
       $customerId = $this->customerSession->getCustomer()->getId();
       if(isset($customerId)){
            return  $this->customerSession->getCanleOrgPopup();
       }else{
           return '';
       }
      
    }
    
    public function getFormAction()
    {
       return $this->getUrl('tncform/Index/Index', ['_secure' => true]);
    }
     
    public function getSaveAction()
    {
       return $this->getUrl('tncform/Index/Save', ['_secure' => true]);
    }
     
     
    public function getChangeHeaderAction()
    {
       return $this->getUrl('tncform/Index/Shopfor', ['_secure' => true]);
    }
    
    public function getValidateTCUrl()
    {
        return $this->getUrl('organization/account/validatetc', ['_secure' => true]);
    }
     
    public function isLoggedIn()
    {
         return $this->customerSession->isLoggedIn();
    }
     
     
    public function Customer_Id()
    {
       return $this->customerSession->getCustomer()->getId();
    }
    
    public function getCustomerId()
    {
       return $this->customerSession->getCustomer()->getId();
    }
    
    /*
     * Returns the vale of the Customer Attribute
     */
    public function Customer_attribute($attribval)
    {
        $customerId= $this->customerSession->getCustomer()->getId();
        if(isset($customerId)){
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $attrib = $customer->getCustomAttribute($attribval);
            if($attrib){
                $attributeval= $customer->getCustomAttribute($attribval)->getValue();
                return $attributeval;
            }
            
        }

    }
    
    
    public function getCustomerAttribute($code)
    {
        $customerId= $this->customerSession->getCustomer()->getId();
        if(isset($customerId)){
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $attrib = $customer->getCustomAttribute($code);
            if($attrib){
                $attributeval= $customer->getCustomAttribute($code)->getValue();
                return $attributeval;
            }
            
        }

    }
    
    /*
    * Retruns the header session value if the User has selected the shop for
    */
    
    public function Session_header_org_terms(){
      if($this->customerSession->getOrgHeaderTerms()){
          return $this->customerSession->getOrgHeaderTerms();
      }else{
          return "";
      }
    }
    
    public function getOrgList(){
        
        return $this->_orgHelper->getOrgWithParent($this->getCustomerId());
        //return $this->_orgHelper->getOrgListByCustomer($this->getCustomerId());
    }
    
    public function getCustomerOrgAdminList(){
        
        $collection = $this->_orgHelper->getOrgListByCustomer($this->getCustomerId());
        $collection->getSelect()
                ->where("main_table.is_admin != 1");
        return $collection;
    }
    
    public function isInitPopupRequired()
    {
        $result = false;
        if($this->isLoggedIn()){
            if ($this->customerSession->getCustomerData()->getCustomAttribute('role')->getValue()) {
                $role = explode(',', $this->customerSession->getCustomerData()->getCustomAttribute('role')->getValue());
                if(array_intersect($role, $this->_orgHelper::ALLOWED_ROLE)){
                    $result = true;
                }
            }
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/terms_popup_block.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Getting result values.".$result);
        $logger->info("Getting customer value.".$this->customerSession->getCustomerId());
       
        return $result;
    }
    
    public function getAdminList()
    {
        $id = $this->customerSession->getTempOrgId();
        if($id){
            return $this->_orgHelper->getOrgAdminList($id);
        } else {
            return false;
        }
        
    }
    
    public function getOrgAdminReq() {
        $id = $this->customerSession->getTempOrgId();
        $collection = $this->orgAdmin->getCollection()
                ->addFieldToFilter('tc_id', $id)
                ->addFieldToFilter('status', 0);

        return $collection;
    }

    public function getOrgData(){
        $id = $this->customerSession->getTempOrgId();
        if($id){
            return $this->_orgHelper->GetOrgById($id);
        } else {
            return false;
        }
    }
    
    public function getIsSubOrg()
    {
        if($this->getAdminList() && $this->getAdminList()->getSize() || !$this->isInitPopupRequired()){
            return true;
        } else {
            return false;
        }
    }
    
    public function getCustomerOrgId()
    {
        return $this->getCustomerAttribute('org_id');
    }
    
    public function getTerms()
    {
        return $this->_coreRegistry->registry('terms');
    }
    
    public function getCDPList(){
        return $this->_orgHelper->getCDPList();
    }
    
    public function setCheckoutOrgPopup(){
        $this->customerSession->setCheckoutOrgConfirmPopup(1);
    }
    
    public function getEtcURL() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        return $this->scopeConfig->getValue("organization/org_setting/distributor_site_url", $storeScope);
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
