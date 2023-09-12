<?php

declare(strict_types=1);

namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Block\Popup;
use Aha\Organization\Helper\Account;
use Aha\Organization\Helper\Data;
use Aha\Organization\Logger\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class ChangeOrg extends Action {

    /**
     * 
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * 
     * @var Logger
     */
    private $logger;

    /**
     * 
     * @var Account
     */
    private $accountHelper;

    /**
     * 
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * 
     * @var Data
     */
    private $orgHelper;

    /**
     * 
     * @var Registry
     */
    private $coreRegistry;

    /**
     * 
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * 
     * @var UrlInterface
     */
    private $urlBuilder;
    private $response;
    private $refererUrl;
    private $terms;

    /**
     * 
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Logger $logger
     * @param Account $accountHelper
     * @param JsonFactory $resultJsonFactory
     * @param Data $orgHelper
     * @param Registry $coreRegistry
     * @param CheckoutSession $checkoutSession
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
            Context $context,
            CustomerSession $customerSession,
            Logger $logger,
            Account $accountHelper,
            JsonFactory $resultJsonFactory,
            Data $orgHelper,
            Registry $coreRegistry,
            CheckoutSession $checkoutSession,
            UrlInterface $urlBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->accountHelper = $accountHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orgHelper = $orgHelper;
        $this->coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute() {
        $resultJson = $this->resultJsonFactory->create();

        $this->response['status'] = false;
        $this->response['terms'] = false;
        try {
            $id = $this->getRequest()->getParam('id');
            if ($this->customerSession->isLoggedIn() && $id) {
                $this->refererUrl = $this->_redirect->getRefererUrl();

                $this->setRefererUrl();
                $this->response['redirect_url'] = $this->refererUrl;

                $this->customerSession->setTerms(1);

                $this->accountHelper->setOrganization($id);
                $this->terms = $this->orgHelper->getTermsPopup();
                $this->coreRegistry->register('terms', $this->terms);
                $this->response['status'] = true;

                $this->setRedirectUrl();

                $this->customerSession->unsRemovedCurrentOrg();

                $this->setBlockContent();

                $this->validateCheckout();

                $this->checkoutSession->unsCustomAddressId();

                return $resultJson->setData($this->response);
            } else {
                return $resultJson->setData($this->response);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function setRefererUrl() {
        try {
            if (strpos($this->refererUrl, "orderconfirmation/index") !== false) {
                $this->refererUrl = $this->urlBuilder->getUrl('sales/order/history/');
            } else {
                $this->refererUrl = $this->_redirect->getRefererUrl();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function setRedirectUrl() {
        try {
            if ($this->customerSession->getCheckoutOrgPopup()) {
                $this->customerSession->setCheckoutOrgConfirmPopup(1);
                $this->response['redirect_url'] = $this->urlBuilder->getUrl('checkout');
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function setCustomerSession() {
        try {
            if ($this->terms['terms'] || $this->terms['subscription']) {
                $this->customerSession->setcheckoutInitPop(1);
            } else {
                $this->customerSession->setCheckoutOrgConfirmPopup(1);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function validateCheckout() {
        try {
            if ($this->checkoutSession->getInCheckout() && !empty(strpos($this->refererUrl, 'checkout/cart'))) {
                $this->setCustomerSession($this->terms);
                $this->checkoutSession->unsInCheckout();
                $this->response['redirect_url'] = $this->urlBuilder->getUrl('checkout');
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function setBlockContent() {
        try {
            if ($this->terms['terms'] || $this->terms['subscription']) {
                $this->response['terms'] = true;
                $layout = $this->_view->getLayout();
                $block = $layout->createBlock(Popup::class);
                $block->setTemplate('Aha_Organization::terms_popup.phtml');
                $this->response['content'] = $block->toHtml();
            } else {
                $this->customerSession->unsTerms();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

}