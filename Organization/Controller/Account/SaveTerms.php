<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class SaveTerms extends Organization
{
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $_scopeConfig;
   
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aha\Organization\Helper\Account $orgAccountHelper,
        \Aha\Organization\Helper\Data $orgHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orgAccountHelper = $orgAccountHelper;
        $this->orgHelper = $orgHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    public function execute() {
        $this->resultJsonFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $response['status'] = false;
        $response['invoicing'] = false;
        $response['redirect_url'] = $this->_redirect->getRefererUrl();
        
        if($this->getRequest()->isPost()){
            $data = $this->getRequest()->getParams();
            $orgId = $this->customerSession->getOrganization();
            
            if(isset($data['terms'])){
                $this->orgAccountHelper->saveTncAcceptance($orgId);
                
                $this->customerSession->unsTempOrgId();
                $this->customerSession->unsOrgData();
            }
            /*
             *  Second Params added to differentiate the location from where the Subscription hits
             *  Need of second param : On Org Creation it goes 0 which makes the user Active subscription to be Inactive
             *  
             */
            if(isset($data['subscription'])){
                $this->orgHelper->saveSubsctiption(1,0); 
            } else {
                $this->orgHelper->saveSubsctiption(0,0);
            }
            $response['status'] = true;
            
            $refererUrl = $this->_redirect->getRefererUrl();
            
            if($this->_checkoutSession->getInCheckout() == 1 && !empty(strpos($refererUrl, 'checkout/cart'))){
                $response['redirect_url'] = $this->urlBuilder->getUrl('checkout');
                if(!empty($this->customerSession->getcheckoutInitPop())){
                    $this->customerSession->setCheckoutOrgConfirmPopup(1);
                }
                $this->_checkoutSession->unsInCheckout();
            } elseif($this->customerSession->getCheckoutOrgPopup() && !empty(strpos($refererUrl, 'checkout/cart'))){
                $this->customerSession->setCheckoutOrgConfirmPopup(1);
                $response['redirect_url'] = $this->urlBuilder->getUrl('checkout');
            } else {
                $response['redirect_url'] = $this->_redirect->getRefererUrl();
            }
        
            if(isset($data['invoicing']) && $data['invoicing']==1){
                $_scopeConfig = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
                $url = $this->_scopeConfig->getValue("organization/org_setting/invoicing_url", $_scopeConfig);
                $response['invoicing'] = true;
                $response['redirect_url'] = $url;
            }
        }
        return $resultJson->setData($response);
    }
}
