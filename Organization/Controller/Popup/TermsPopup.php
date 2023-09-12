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
use Aha\Organization\Helper\Data;
use Magento\Framework\Registry;

class TermsPopup extends Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    
    protected $_logger;
    
    /**
     * 
     * @param Context $context
     * @param Session $customerSession
     * @param Logger $logger
     * @param Data $orgHelper
     * @param Registry $coreRegistry
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Logger $logger,
        Data $orgHelper,
        Registry $coreRegistry,
        JsonFactory $resultJsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->_logger = $logger;
        $this->orgHelper = $orgHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    
    public function execute() {
        $resultData = [
            'status' => 'failed',
            'status_code' => 0,
            'message' => __('')
        ];
        $result = $this->resultJsonFactory->create();
        if($this->customerSession->isLoggedIn()){
            $terms = $this->orgHelper->getTermsPopup();
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/terms_popup_controller.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info("Getting Terms values.".$terms['terms']);
            $logger->info("Getting subscription values.".$terms['subscription']);
            $logger->info("Getting customer value.".$this->customerSession->getCustomerId());
            $this->_coreRegistry->register('terms', $terms);
            $resultData = [
                'status' => 'success',
                'status_code' => 0,
                'message' => __('success')
            ];
            if($terms['terms'] || $terms['subscription']){
                $this->customerSession->setTerms(1);
                $layout = $this->_view->getLayout();
                $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);

                if(!empty($this->customerSession->getOrgData())){
                    $block->setTemplate('Aha_Organization::org_terms.phtml');
                } else {
                    $block->setTemplate('Aha_Organization::terms_popup.phtml');
                }

                //$this->getResponse()->setBody($block->toHtml());
                $resultData['status_code'] = 1;
                $resultData['content'] = $block->toHtml();
                return $result->setData($resultData);
            } else {
                $resultData['status_code'] = 2;
                $resultData['message'] = __('Already accepted');
                $this->customerSession->unsTerms();
                return $result->setData($resultData);
            }
        } else {
            $resultData['message'] = __('Customer session timeout');
            return $result->setData($resultData);
        }
    }

}
