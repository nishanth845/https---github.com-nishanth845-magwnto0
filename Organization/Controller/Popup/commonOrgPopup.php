<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;
use Aha\Organization\Logger\Logger;

class commonOrgPopup extends Action
{
    /**
     * 
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $resultJsonFactory,
        Logger $logger
    ) {
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }
    
    
    public function execute() {
        $this->resultJsonFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $response['status'] = false;
        
        if($this->customerSession->isLoggedIn()){
            $layout = $this->_view->getLayout();
            $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
            $block->setTemplate('Aha_Organization::common_org_popup.phtml');
            $response['content'] = $block->toHtml();
            $response['status'] = true;

            return $resultJson->setData($response);
        } else {
            return $resultJson->setData($response);
        }
    }

}