<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Controller\Organization;

class ValidateCdp extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aha\Organization\Model\Cdp $cdpModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cdpModel = $cdpModel;
        $this->storeManager = $storeManager;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    public function execute() {
        $this->resultJsonFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $response['status'] = false;
        
        $id = $this->getRequest()->getParam('id');
        $groupId = $this->storeManager->getStore()->getGroupId();
        
        if($id){
            $id = trim($id);
            $cdp = $this->cdpModel->getCollection()
                    ->addFieldToFilter('purchase_code', $id)
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('group_id', $groupId)
                    ->getFirstItem(); 
            if ($cdp->hasData()) {
                $response['status'] = true;
                $response['cdp_id'] = $cdp->getId();
            } else {
                $response['message'] = "Invalid Purchase Code. Please try again.";
            }
        } else if(empty($id)) {
            $response['message'] = "This is a required field.";
        }else {
            $response['message'] = "Invalid Purchase Code. Please try again.";
        }

        return $resultJson->setData($response);
    }

}