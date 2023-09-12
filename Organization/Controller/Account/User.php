<?php
/**
 *
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 */
namespace Aha\Organization\Controller\Account;
use Aha\Organization\Controller\Organization;
use Aha\Organization\Helper\Data as OrgData;

class User extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        OrgData $orgHelper,
        \Aha\Organization\Model\Organization $orgModel) 
    {
        $this->orgModel = $orgModel;
        $this->_orghelper = $orgHelper;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Organization - User Management'));
        
        $org_id = $this->getRequest()->getParam('id');
        $customerId = !empty($this->customerSession->getId()) ? $this->customerSession->getId() :  null;
                
        $mappedCustomer = $this->_orghelper->validateCustomerOrgAdmin($org_id, $customerId);
        
        if (!$mappedCustomer) {
            $this->messageManager->addErrorMessage(__('You are not having access to view the Organization.'));
            $this->_redirect('*/account');
            return;
        }
        
        try {
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
                $breadcrumbs->addCrumb('usermanagement', [
                    'label' => __('User Management'),
                    'title' => __('User Management')
                    ]
                );
            }
        
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('organization/account');
        }
  
    	return $resultPage;
    }
}
