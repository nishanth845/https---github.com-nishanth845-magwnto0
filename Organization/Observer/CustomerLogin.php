<?php

namespace Aha\Organization\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Model\OrgAdmin $orgAdmin,
        \Aha\Organization\Model\OrganizationCustomer $orgCustomer
    ) {
        $this->customerSession = $customerSession;
        $this->orgAdmin = $orgAdmin;
        $this->orgCustomer = $orgCustomer;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $customer = $observer->getEvent()->getCustomer();

        $orgAdminCollection = $this->orgAdmin->getCollection()
                ->addFieldToFilter('email', $customer->getEmail())
                ->addFieldToFilter('status', 0);

        if($orgAdminCollection->getSize()){
            foreach ($orgAdminCollection as $orgAdmin){
                $orgExist = $this->isOrgAdminExist($orgAdmin->getTcId(), $customer->getId());
                if($orgExist){
                    $model = $this->orgCustomer;
                    $model->setParentId($orgAdmin->getTcId())
                            ->setCustomerId($customer->getId())
                            ->setIsAdmin(1)
                            ->save();

                    $model->unsetData();
                }
                $orgAdmin->setStatus(1)->save();
            }
        }
    }

    public function isOrgAdminExist($orgId, $customerId)
    {
        $orgAdmincollection = $this->orgCustomer->getCollection()
                ->addFieldToFilter('parent_id', $orgId)
                ->addFieldToFilter('customer_id', $customerId);

        foreach($orgAdmincollection as $orgAdmin){
            if($orgAdmin->getIsAdmin() == 0){
                $orgAdmin->setIsAdmin(1)->save();
            }
        }
        if($orgAdmincollection->getSize() > 0){
            return false;
        }
        return true;
    }
}