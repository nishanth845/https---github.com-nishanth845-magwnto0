<?php

declare(strict_types=1);

namespace Aha\Organization\Controller\Address;

use \Aha\Organization\Model\Address as OrgAddress;
use \Magento\Framework\App\Action\Context;
use \Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Aha\Organization\Model\Organization;
use \Magento\Framework\Message\ManagerInterface;

class Delete extends \Magento\Framework\App\Action\Action
{
    private $_addressRepository;
    private $_customerSession;
    private $resultJsonFactory;
    private $orgData;
    private $orgAddressResourceModel;
    protected $messageManager;

    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        Session $customerSession,
        JsonFactory $resultJsonFactory,
        Organization $orgData,
        OrgAddress $orgAddressResourceModel,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_addressRepository = $addressRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerSession = $customerSession;
        $this->orgAddressResourceModel = $orgAddressResourceModel;
        $this->orgData = $orgData;
        $this->messageManager = $messageManager;
    }

    /**
     * @return Magento\Framework\App\ResponseInterface|Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->getRequest()->isAjax()) {
            return $resultJson->setData(['success' => false]);
        }
        $addressId = $this->getRequest()->getParam('id', false);
        $orgId='';

        if ($addressId) {
            try {
                $address = $this->_addressRepository->getById($addressId);
                $orgId = $this->_customerSession->getOrganization();
                $orgFlag=0;
                if (($orgId != 'Myself') && ($orgId !='')) {
                  
                    $orgData = $this->orgData->load($orgId);
                    if ($orgData->getDefaultBilling()==$addressId) {
                        $orgData->setDefaultBilling(null);
                        $orgFlag=1;
                    }
                    if ($orgData->getDefaultShipping()==$addressId) {
                        $orgData->setDefaultShipping(null);
                        $orgFlag=1;
                    }
                    if ($orgFlag) {
                        $orgData->save();
                    }
                    $orgAddressResourceModelObj = $this->orgAddressResourceModel->load($addressId)->delete();
                    $this->_addressRepository->deleteById($addressId); 
                    $this->messageManager->addSuccess(__('You deleted the address.'));
                } else {
                    if ($address->getCustomerId() === $this->_customerSession->getCustomerId()) {
                        $this->_addressRepository->deleteById($addressId); 
                        $this->messageManager->addSuccess(__('You deleted the address.'));
                    } else {
                        $this->messageManager->addError(__('We can\'t delete the address right now.'));
                    }
                }
                
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t delete the address right now.'.$other->getMessage()));
            }
            return $resultJson->setData(['success' => true]);
        } else {
            return $resultJson->setData(['success' => false]);
        }
    }
}