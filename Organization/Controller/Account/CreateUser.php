<?php
/**
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class CreateUser extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Logger\Logger $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Helper\Data $orgHelper,
         \Aha\Organization\Model\Organization $orgModel
    ) {
        $this->orgHelper = $orgHelper;
        $this->orgModel = $orgModel;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    public function execute() {
        $id = $this->getRequest()->getParam('id');
        $resultPage = $this->resultPageFactory->create();
        
        $org_id = $this->getRequest()->getParam('id');
       
        if($org_id){
            $org = $this->orgModel->load($org_id);
            $org_name =  $org->getOrgName();
            if(strlen((string)$org_name) > 25){
               $org_name =  substr($org->getOrgName(), 0, 25).'...';
            }else{
               $org_name =  $org->getOrgName(); 
            }
            
            $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('account.link', [
                'label' => __('My Organization'),
                'title' => __('My Organization'),
                'link' => $this->_url->getUrl('organization/account')
                    ]
            );
            $breadcrumbs->addCrumb('remhyperorgname', [
                'label' => __($org_name),
                'title' => __($org->getOrgName()),
                'link' => '#'
                ]
            );
            $breadcrumbs->addCrumb('users', [
                'label' => __('Add User'),
                'title' => __('Add User')
                ]
            );
       }



        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        
        if ($navigationBlock) {
            $navigationBlock->setActive('organization/account');
        }
        
        if($id){
            if(!$this->orgHelper->validateOrgAdmin($id, $this->customerSession->getId())) {
                $this->messageManager->addError(__('You are not having access to add user to that Organization'));
                $this->_redirect('*/*/index/');
            }
            //$resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('My Organization - Add New User'));
            return $resultPage;
        } else {
            $this->_redirect('*/*/index/');
            return;
        }
        
    }

}
