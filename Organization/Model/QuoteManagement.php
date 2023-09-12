<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aha\Organization\Model;


/**
 * Class QuoteManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{
   
    /**
     * Prepare quote for customer order submit
     *
     * @param Quote $quote
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCustomerQuote($quote)
    {
        /** @var Quote $quote */
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $this->customerRepository->getById($quote->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        //$logger->info('SASI I AM FROM shipping'.$shipping->getSameAsBilling().'--'.$shipping->getSaveInAddressBook().'---'.$shipping->getCustomerId());
        if ($shipping != null) {
            if (!$shipping->getSameAsBilling()) {
                $shipping->setSameAsBilling(0);
            }
            $shipping->setSaveInAddressBook(0);
            $shipping->save();
        }
        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            //save here new customer address
            $shippingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddress);
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
            $this->addressesToSync[] = $shippingAddress->getId();
            $shipping->setCustomerAddressId($shippingAddress->getId());
        }
        $billing->setSameAsBilling(0);
        $billing->setSaveInAddressBook(0);
        $billing->save();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $billingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($billingAddress);
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
            $this->addressesToSync[] = $billingAddress->getId();
            $billing->setCustomerAddressId($billingAddress->getId());
        }
        if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
            $shipping->setIsDefaultBilling(true);
        }
    }
}
