<?php
/*
 * Filename     : Save.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Controller\Adminhtml\Index;

use Magento\Framework\App\ResourceConnection;
use Aha\BillingPlatform\Helper\OrderData;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action {

    protected $connection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aha\Organization\Model\Organization $orgModel,
        \Aha\Organization\Helper\Data $helper,
        \Aha\Forestna\Helper\Data $forestna_helper,
        \Aha\BillingPlatform\Helper\OrganizationData $organizationoatahelper,
        OrderData $helperorderData,
        ResourceConnection $resource
    ) {
        $this->orgModel = $orgModel;
        $this->helper = $helper;
        $this->backSession = $context->getSession();
        $this->forestna_helper = $forestna_helper;
        $this->organizationoatahelper = $organizationoatahelper;
        $this->_helperorderData = $helperorderData;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/account-setup.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
        $this->connection = $resource->getConnection();
        parent::__construct($context);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
        $data = $this->getRequest()->getPostValue();
        $defaultBilling = false;
        $accountDetails = '';
        if (isset($data) && !empty($data)) {
            $id = $this->getRequest()->getParam('id');
            $vat = $data['tax_number'];
            if (!empty($vat)) {
                $this->logger->info('VAT number : ',$vat);
                $uVat = str_replace('/','-',$vat);
                if (!preg_match('/^[a-zA-Z0-9.-]+$/iD',$uVat)) {
                    $this->logger->info('Invalid VAT number');
                    $this->messageManager->addError(__('Enter a valid Tax ID or VAT number.'));
                    $this->_redirect('*/*/edit', ['id' => $id, '_current' => true]);
                    return;
                    $this->logger->info('after redirect to save org');
                }
                $this->logger->info('valid VAT number');
            }
            $model = $this->orgModel;
            if ($id) {
                $this->logger->info('org id : ',$id);
                $model->load($id);
                if (array_key_exists('address',$data)) {
                    foreach ($data['address'] as $address) {
                        if ($address['default_billing']=="true") {
                            $defaultBilling = $address['default_billing'];
                        }
                    }
                } else {
                    $this->messageManager->addError(__('Organization is not saved. There must be default billing address.'));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }
                $org_code = $model->getData('morg_id') ?? "";
                if ($org_code !='') {
                    $newentity_id = '';
                    $accountDetails = $this->_helperorderData->getAccountIdBP($org_code);
                    if ($accountDetails!=null || $accountDetails !='') {
                        $oldDefaultBillingAddress = $model->getData('default_billing');
                        foreach ($data['address'] as $address) {
                            if ($address['default_billing']=="true") {
                                $defaultBilling = $address['default_billing'];
                                if(array_key_exists('entity_id',$address))
                                {
                                    $newentity_id = $address['entity_id'];
                                }
                                if($oldDefaultBillingAddress != $newentity_id){
                                    $this->messageManager->addError(__('Organization is not saved. You can not change Default Billing Address.'));
                                    $this->_getSession()->setFormData($data);
                                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                                    return;  
                                }
                                $newCountryId = $address['country_id'];
                                $oldCountryId = $this->getCountryAddressById($address['entity_id']);
                                if ($newCountryId != $oldCountryId) {
                                    $this->messageManager->addError(__('Organization is not saved. You can not change country.'));
                                    $this->_getSession()->setFormData($data);
                                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                                    return;
                                }
                            }
                        }
                    }
                }
                if ($defaultBilling == false) {
                    $this->messageManager->addError(__('Organization is not saved. Default Billing Address is not set.'));
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }
                
                if (!empty($model->getData())) {
                    $this->logger->info('model data ln 61: ',print_r($model->getData(), true));
                } else {
                    $this->logger->info('model data is empty ln 64 ');
                }
            }   
            $this->logger->info('org data : ', print_r($data, true));
            $model->addData($data);
            if (!empty($model->getData())) {
                    $this->logger->info('model data ln 70: ',print_r($model->getData(), true));
                } else {
                    $this->logger->info('model data is empty ln 72 ');
                }
            if (!$id) {
                $model->setOrgType(2);
                $model->setMorgId(uniqid("ECOM-"));
            }

            if($data['store_id'] != 0)
            {
                $model->setWebsiteId($this->helper->getWebsiteId($data['store_id']));
            }
            
            try {
                $createInForest = false;
                $updateInForest = false;                
                
                if(empty($model->getForestnaId()) && $data['po_request'] == 1)
                {                        
                    if(empty(floatval($data['allocated_credit_limit'])))
                    {
                        $default_credit_limit = $this->forestna_helper->getConfig("forest/customer/ccl");
                        $model->setAllocatedCreditLimit($default_credit_limit);
                    }
                    $createInForest = true;
                }
                elseif(!empty($model->getForestnaId())){
                    $updateInForest = true;
                }
                if(empty(floatval($data['allocated_credit_limit'])))
                {
                    $default_credit_limit = $this->forestna_helper->getConfig("forest/customer/ccl");
                    $model->setAllocatedCreditLimit($default_credit_limit);
                }
                
                if($data['po_request'] == 1){
                    $model->setPoStatus(1);
                } else {
                    $model->setPoStatus(0);
                }
                $this->logger->info('Organization created by admin controller');               
                $model->save();                
                $this->helper->MapCustomer($data, $model);
                
                if(isset($data['address'])){
                    $this->helper->deleteAddress($data, $model->getId());

                    $this->helper->SaveAddress($data, $model);
                    $model->setHasAddress(1)->save();
                } else {
                    $model->setHasAddress(0)->save();
                    $this->helper->deleteAddress($data, $model->getId());
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }
                $moreMessage = "";
                if($createInForest)
                {
                    $org_id = $model->getId();
                    $customerEmail = $this->_auth->getUser()->getEmail();
                    $cust_created = $this->forestna_helper->createForestNaCustomer($org_id,$customerEmail);
                    if(!empty($cust_created))
                    {
                        $moreMessage = " Customer account in ForestNa is also created.";
                    }
                    if($cust_created === false)
                    {
                        $moreMessage = " But could not create Customer account in ForestNa.";
                    }
                }
                elseif($updateInForest)
                {                    
                    $cust_updated = $this->forestna_helper->updateForestNaCustomer($model);
                    if($cust_updated == true)
                    {
                        $moreMessage = " Also updated in ForestNa.";
                    }
                    else
                    {
                        $moreMessage = " But could not update in ForestNa.";
                    }
                }

                if(empty($id)){
                    $id = $model->getId();
                }
              
                if($this->organizationoatahelper->isBillingPlatformEnabled()){
                    if( $bpsaveresponsedata = $this->organizationoatahelper->createOrg($id)){
                        if(!empty($bpsaveresponsedata)){
                            if(!empty($bpsaveresponsedata['errormessage'])){
                                    $this->messageManager->addError($bpsaveresponsedata['errormessage']);
                                }
                            if(!empty($bpsaveresponsedata['successmessage'])){
                                    $moreMessage = $bpsaveresponsedata['successmessage'];
                                }
                            if(!empty($bpsaveresponsedata['notice'])){
                                    $this->messageManager->addNotice(__($bpsaveresponsedata['notice']));
                                }   
                        }
                    }
                }

                $this->messageManager->addSuccess(__('Organization has been saved.'.$moreMessage));
                $this->backSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $e->getMessage();
                $this->messageManager->addException($e, __('Something went wrong while saving the Organization.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            return;
        }
        $this->_redirect('*/*/');
    }

    public function getCountryAddressById($id)
    {
        $tableName = $this->connection->getTableName('customer_address_entity');

        $select = $this->connection->select()
            ->from($tableName, ['country_id'])
            ->where('entity_id = ?', $id);

        return $this->connection->fetchOne($select);
    }
}
