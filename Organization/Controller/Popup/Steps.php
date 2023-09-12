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
use \Magento\Store\Model\StoreManagerInterface;

class Steps extends Action
{
    /**
     * 
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    public function execute() {
        $resultJson = $this->resultJsonFactory->create();
        $response['status'] = false;
        
        if($this->customerSession->isLoggedIn()){
            $step = $this->getRequest()->getParam('step');
            $template = $this->getTemplate($step);
            $layout = $this->_view->getLayout();
            if($step == 2){
                $block = $layout->createBlock(\Aha\Organization\Block\Account\Address::class);
            } else {
                $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
            }
            $block->setTemplate($template);
            //$this->getResponse()->setBody($block->toHtml());
            
            $response['content'] = $block->toHtml();
            $response['status'] = true;

            return $resultJson->setData($response);
        } else {
            return $resultJson->setData($response);
        }
    }
    
    public function getTemplate($step) 
    {
        switch ($step){
            case 1: return 'Aha_Organization::account/step_one.phtml';
            case 2: return 'Aha_Organization::account/step_two.phtml';
            case 3: 
                if( $this->storeManager->getWebsite()->getCode() == \Aha\StoreSwitcher\Helper\Data::INSIGHTS_WEBSITE_CODE) {
                    return'Aha_Organization::account/step_three_ipa.phtml';
                } else {
                    return'Aha_Organization::account/step_three.phtml';
                }
            default: return 'Aha_Organization::account/step_one.phtml';
        }
    }

}