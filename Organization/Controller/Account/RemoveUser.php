<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;
use Aha\Organization\Helper\Data as OrgData;
use Magento\Customer\Api\CustomerRepositoryInterface;

class RemoveUser extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Aha\Organization\Model\OrgAdmin $orgAdmin,
        \Aha\Organization\Model\OrganizationCustomer $orgCustomer,
        \Aha\Organization\Model\Organization $organization,
        OrgData $orgHelper,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->orgAdmin = $orgAdmin;
        $this->orgCustomer = $orgCustomer;
        $this->organization = $organization;
        $this->_orghelper = $orgHelper;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    public function execute() {
        $adminCount = 0;
        $email = '';
        $orgName = $this->getRequest()->getParam('org');
        $org_id = $this->getRequest()->getParam('orgid');
        $customerId = !empty($this->customerSession->getId()) ? $this->customerSession->getId() :  null;
        
        $mappedCustomer = $this->_orghelper->validateCustomerOrgAdmin($org_id, $customerId);
        
        if (!$mappedCustomer) {
            $this->messageManager->addErrorMessage(__('Access Denied to remove user from this Organization.'));
            $this->_redirect('*/account');
            return;
        }
        
        if($this->getRequest()->getParam('id')){
            $orgCustomer = $this->orgCustomer->load($this->getRequest()->getParam('id'));

            if(!$orgCustomer->hasData()) {
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
            $customer = $this->_customerRepositoryInterface->getById($orgCustomer->getCustomerId());
            $email = $customer->getEmail();

            if($this->customerSession->getOrganization() == $orgCustomer->getParentId()){
                $this->customerSession->setRemovedCurrentOrg(1);
            }
            
            if($orgCustomer->getRole()){
                $orgCustomer->setIsAdmin(0)->save();
            } else {
                $orgCustomer->delete();
            }

            $orgId = $orgCustomer->getParentId();
            /* $orgCustomerCollection = $this->orgCustomer->getCollection()
                    ->addFieldToFilter('parent_id', $orgId)
                    ->addFieldToFilter('is_admin', 1); */

            $adminCount = $this->getUserCount($orgId);

        } else if($this->getRequest()->getParam('uid')){
            $orgCustomer = $this->orgAdmin->load($this->getRequest()->getParam('uid'));
            $email = $orgCustomer->getEmail();

            $orgCustomer->delete();
            $orgId = $orgCustomer->getTcId();
            /* $orgCustomerCollection = $this->orgAdmin->getCollection()
                    ->addFieldToFilter('tc_id', $orgId); */

            $adminCount = $this->getUserCount($orgId);
        }

        if($adminCount == 0 && !empty($orgId)){
            $organization = $this->organization->load($orgId);
            $organization->setTncStatus(0)->save();
        }
        
        if (!empty($email) && !empty($orgName)) {
            $this->messageManager->addSuccessMessage(__("Removed $email as an Admin for $orgName.")); 
        } else {
            $this->messageManager->addSuccessMessage(__("Removed User Successfully!!"));
        }
        $this->_redirect('*/account');
        return;
    }
    
    public function getUserCount($id) 
    {
        if(isset($this->userCount[$id])){
            return $this->userCount[$id];
        }
        
        $collection = $this->orgCustomer->getCollection()
                ->addFieldToFilter('parent_id', $id)
                ->addFieldToFilter('is_admin', 1);
        
        $orgAdminCollection = $this->orgAdmin->getCollection()
                ->addFieldToFilter('tc_id', $id)
                ->addFieldToFilter('status', 0);

        return $this->userCount[$id] = $collection->getSize() + $orgAdminCollection->getSize();
    }

}
