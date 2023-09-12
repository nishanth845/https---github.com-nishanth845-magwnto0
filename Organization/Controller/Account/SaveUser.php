<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class SaveUser extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Aha\Organization\Model\OrgAdmin $orgAdmin,
        \Aha\Organization\Model\OrganizationCustomer $orgCustomer,
        \Magento\Customer\Model\Customer $customer,
        \Aha\Organization\Helper\Data $orgHelper
    ) {
        $this->orgAdmin = $orgAdmin;
        $this->orgCustomer = $orgCustomer;
        $this->customer = $customer;
        $this->orgHelper = $orgHelper;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    public function execute() {
        $customerId = $this->customerSession->getId();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            if (!$this->orgHelper->validateOrgAdmin($data['tc_id'], $customerId)) {
                $this->messageManager->addError(__('You are not having access to add user to that Organization'));
                $this->_redirect('*/*/index/');
            } else {
                // Remove all illegal characters from email
                $validateEmail = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
                // Validate e-mail
                if (!filter_var($validateEmail, FILTER_VALIDATE_EMAIL) && empty($data['u_name'])) {
                    $this->messageManager->addError(__('Required Fields are Missing.'));
                    $this->_redirect('*/account/createuser', ['id' => $data['tc_id']]);
                } else {
                    // $email = $data['email']; // this will ignore the email validation what we did in line #39
                    $data['u_name'] = strip_tags($data['u_name']);
                    $data['email'] = $validateEmail;
                    $this->mapCustomer($data);
                    $this->_redirect('*/account/user', ['id' => $data['tc_id']]);
                }
            }
        } else {
            $this->_redirect('*/account');
        }
        return;
    }
    
    public function isCustomerExist($email) {
        $customer = $this->customer->setWebsiteId(1)->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        
        return false;
    }
    
    public function mapCustomer($data)
    {
        $email = $data['email'];
        $orgName = $data['orgname'];
        $customer = $this->isCustomerExist($email);
        
        if($customer){
            $orgAdmin = $this->getOrgAdmin($customer->getId(), $data['tc_id']);
            if($orgAdmin->getId()){
                if($orgAdmin->getIsAdmin()){
                    $this->messageManager->addError(__("The email address you entered is already a user for this $orgName."));
                } else {
                    $orgAdmin->setIsAdmin(1)->save();
                    $this->messageManager->addSuccess(__("$email added as an Admin for $orgName."));
                }
            } else{
                $this->orgCustomer
                        ->setParentId($data['tc_id'])
                        ->setCustomerId($customer->getId())
                        ->setIsAdmin(1)
                        ->save();
                $this->messageManager->addSuccess(__("$email added as an Admin for $orgName."));
            }
        } else {
            $orgAdminReq = $this->orgAdmin->getCollection()
                    ->addFieldToFilter('email', $email)
                    ->addFieldToFilter('tc_id', $data['tc_id'])
                    ->getFirstItem();

            if($orgAdminReq->getId()){
                $this->messageManager->addError(__("The email address you entered is already a user for this $orgName."));
            } else {
                $this->orgAdmin
                        ->setData($data)
                        ->save();
                $this->messageManager->addSuccess(__("$email added as an Admin for $orgName."));
            }
        }
        return;
    }


    public function getOrgAdmin($customerId, $orgId) {
        return $this->orgCustomer->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();
    }
}
