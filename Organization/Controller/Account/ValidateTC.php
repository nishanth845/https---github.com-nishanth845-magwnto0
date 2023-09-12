<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class ValidateTC extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aha\Organization\Helper\Data $orgHelper,
        \Aha\Organization\Model\Organization $orgModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orgHelper = $orgHelper;
        $this->orgModel = $orgModel;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }

    public function execute() 
    {
        $this->customerSession->unsTempOrgId();
        $resultData = array();
        $resultData['status'] = true;
        
        $id = $this->getRequest()->getParam('id');

        if($id){
            $orgTempData = $this->customerSession->getOrgData();

            if(isset($orgTempData['tc_id_number']) && $orgTempData['tc_id_number'] != $id || !isset($orgTempData['tc_id_number'])){
                $this->customerSession->unsOrgData();
                $this->customerSession->setOrgData(array('tc_id_number' => $id));
            }
            $orgData = $this->orgModel->load($id, 'tc_id_number');

            if($orgData->getId() && $orgData->getEtc()){
                $layout = $this->_view->getLayout();
                $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
                $block->setTemplate('Aha_Organization::account/etc.phtml');
                //$this->getResponse()->setBody($block->toHtml());
                $resultData['content'] = $block->toHtml();
            /*} elseif ($orgData->getId() && $this->accountEsist($orgData->getId())){
                $this->customerSession->setTempOrgId($orgData->getId());
                $layout = $this->_view->getLayout();
                $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
                $block->setTemplate('Aha_Organization::account/org-exist.phtml');
                $resultData['content'] = $block->toHtml();*/
            } elseif($orgData->getId()) {
                $this->customerSession->setTempOrgId($orgData->getId());
                $layout = $this->_view->getLayout();
                $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
                $block->setTemplate('Aha_Organization::account/step_one.phtml');
                $resultData['content'] = $block->toHtml();
            } else {
                $resultData['status'] = false;
                $resultData['message'] = "Please enter valid TC ID or continue without TC ID.";
            }
            
        }
        
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($resultData);
        return $jsonResult;
    }
    
    public function accountEsist($id){
        $collection = $this->orgHelper->getCollectionByOrgId($id)
                ->addFieldToFilter('is_admin',1);
        
        if($collection->getSize()){
            return true;
        } else {
            return false;
        }
    }
    
    

}
