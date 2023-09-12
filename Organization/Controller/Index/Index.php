<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Index;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
  
    /*
     * $orgpopup => will show the Organization popup
     * $termspopupreq => 1 to display the terms popup
     * $termsReq => 1 to display the Terms & condition Required in the Popup
     * $showchange =>1 to show whethere change options is required in the popup
     * 
     * $headertermsSelect is used to check for the session header organization value save in Session
     * will be unset in Proceed ,save skip & cancle  options
     * 
     */
    
    public function execute()
    {
        if($this->customerSession->isLoggedIn()) {
            $layout = $this->_view->getLayout();
            $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
            $block->setTemplate('Aha_Organization::org_popup.phtml');
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
