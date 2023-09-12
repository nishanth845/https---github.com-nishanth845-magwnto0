<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class Save extends Organization
{
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Aha\Organization\Helper\Account $accountHelper,
        \Aha\Organization\Helper\Data $orgHelper,
        \Aha\Organization\Model\Organization $orgModel
    ) {
        $this->accountHelper = $accountHelper;
        $this->orgHelper = $orgHelper;
        $this->orgModel = $orgModel;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }
    
    public function execute() {
        if($this->customerSession->getOrgData()){
            $data = $this->getRequest()->getParams();
            $postData = $this->customerSession->getOrgData();
            if($postData['suborg'] == 1){
                
            }

            //save organization data
            $orgData = $this->saveOrgData($postData);
            $postData['tid'] = $orgData->getId();
            
            //save address
            $saveAddress = $this->accountHelper->saveAddress($postData);
            
            //update default billing address
            if($saveAddress['status'] && $orgData->getDafaultBilling() != $saveAddress['address_id']){
                $this->_logger->info('Saving default billing and shipping address');
                $orgData->setDefaultBilling($saveAddress['address_id'])->save();
                $orgData->setDefaultShipping($saveAddress['address_id'])->save();
            }
            //Set allocate credit limit
            $defaultcreditlimit = $this->orgHelper->getDefaultCreditLimit();
            if($defaultcreditlimit){
                $orgData->setAllocatedCreditLimit($defaultcreditlimit)->save();
            }
            //make the customer as a admin for this organization
            $this->accountHelper->mapOrg($orgData->getId(), $this->customerSession->getId());
            
            //setOrganization
            $this->accountHelper->setOrganization($orgData->getId(), $orgData->getCustomerGroup());
            
            if(isset($data['purchase-code'])){
                $this->accountHelper->setCDP($orgData->getId(), $data);

                $this->saveCdp($orgData);                
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Create an Account'));
    	return $resultPage;
    }
    
    public function saveOrgData($data)
    {
        
        $model = $this->orgModel;
        
        if(!empty($data['tc-id']) && !$data['suborg']){
            $model = $this->orgModel->load($data['tc-id']);
        } else {
            $model->setMorgId(uniqid("ECOM-"));
        }
        
        $model->setStoreId($this->orgHelper->getStoreId())
             ->setWebsiteId($this->orgHelper->getWebsiteId());

        if(empty($data['tc-id'])){
            $model->setOrgType(2);
        }
        if($data['suborg']){
            $model->setTcId($data['tc_id'])
                    ->setTcIdNumber($data['tc_id_number'])
                    ->setSecurityId($data['security_id'])
                    ->setItcCode($data['itc_code'])
                    ->setOrgType(1)
                    ->setStoreId($data['store_id'])
                    ->setWebsiteId($data['website_id']);
        }
        
        if(isset($data['tax_exemption_applied'])){
            $model->setAppliedTaxExempt($data['tax_exemption_applied']);
        }
        
        $model->setSubOrg($data['suborg'])
                ->setOrgName(strip_tags(str_replace("%"," ",$data['org_name'])))
                ->setTaxNumber(strip_tags($data['tax_number']))
                ->setBillingTo(strip_tags($data['billing_to']))
                ->setBillingDept(strip_tags($data['billing_dept']))
                ->setBillingEmail(strip_tags($data['billing_email']))
                ->setBillingEmailCc(strip_tags($data['billing_email_cc']))
                ->setDefaultBilling(strip_tags($data['address_id']));
        if(empty($model->getCustomerGroup())){
            $model->setCustomerGroup(1);
        }
        
        try{
            $this->_logger->info('Organisation created from Frontend Controller');
            return $model->save();
        } catch (\Exception $ex) {
            $this->_logger->info($ex->getMessage());
            return false;
        }
    }
    
    public function saveCdp($orgData)
    {        
        if (!$orgData->getHasCdp()) {
            $orgData->setHasCdp(1);
        }
        
        if (!$orgData->getHasMultipleCdp()) {
            $orgCdpList = $this->orgHelper->getCDPListByTcId($orgData->getId());
            if ($orgCdpList && $orgCdpList->getSize() > 1) {
                $orgData->setHasMultipleCdp(1);
            }
        }
        
        if (!$orgData->getHasCdp() || !$orgData->getHasMultipleCdp()) {
            $this->_logger->info('Save organisation has CDP');
            $orgData->save();
        }
    }

}
