<?php

declare(strict_types=1);

namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Model\Cdp as PurchaseCode;
use Aha\Organization\Model\CdpCustomer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class SetCDP extends Action {
    
    /**
     * 
     * @var Session
     */
    protected $customerSession;
    
    /**
     * 
     * @var CdpCustomer
     */
    protected $cdpCustomer;
    
    /**
     * 
     * @var PurchaseCode
     */
    protected $purchaseCode;
    
    /**
     * 
     * @var CheckoutSession
     */
    protected $checkoutSession;
    
    /**
     * 
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * 
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
            Context $context,
            Session $customerSession,
            CdpCustomer $cdpCustomer,
            PurchaseCode $purchaseCode,
            CheckoutSession $checkoutSession,
            JsonFactory $resultJsonFactory,
            StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->cdpCustomer = $cdpCustomer;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->purchaseCode = $purchaseCode;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute() {
        $this->resultJsonFactory->create();
        $resultJson = $this->resultJsonFactory->create();

        $cdpId = $this->getRequest()->getParam('id');
        $purchaseCode = $this->getRequest()->getParam('pcode');

        $customerId = $this->customerSession->getCustomer()->getId();
        $orgId = $this->customerSession->getOrganization();

        if (!empty($cdpId) && !empty($orgId) && $orgId != "Myself") {
            if ($this->validatePurchaseCode($purchaseCode)) {
                $this->mapCDPtoCustomer($customerId, $orgId, $cdpId);
                $this->checkoutSession->setPurchaseCode($purchaseCode);
                $response['status'] = true;
            } else {
                $response['status'] = false;
                $response['message'] = "Invalid Purchase Code. Please try again.";
            }
        } else {
            $this->checkoutSession->unsPurchaseCode();
            $response['status'] = false;
            $response['message'] = "Invalid request";
        }
        return $resultJson->setData($response);
    }

    public function mapCDPtoCustomer($customerId, $orgId, $cdpId) {
        $cdpCollection = $this->cdpCustomer->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('tc_id', $orgId)
                ->addFieldToFilter('cdp_id', $cdpId)
                ->getFirstItem();

        if (!$cdpCollection->hasData()) {
            $this->cdpCustomer->setTcId($orgId)
                    ->setCustomerId($customerId)
                    ->setCdpId($cdpId);
            $this->cdpCustomer->save();
        }
        return true;
    }

    private function validatePurchaseCode($purchaseCode) {
        $groupId = $this->storeManager->getStore()->getGroupId();
        $codeCollection = $this->purchaseCode->getCollection()
                ->addFieldToFilter('purchase_code', $purchaseCode)
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('group_id', $groupId)
                ->getFirstItem();
        if ($codeCollection->hasData()) {
            return true;
        } else {
            return false;
        }
    }

}
