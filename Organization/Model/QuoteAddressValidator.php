<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aha\Organization\Model;

use Magento\Quote\Model\QuoteAddressValidator As quoteaddressvalidate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Quote shipping/billing address validator service.
 *
 */
class QuoteAddressValidator extends quoteaddressvalidate
{
    /**
     * Address factory.
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Customer repository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository Customer repository.
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

   
    /**
     * Validate address.
     *
     * @param AddressInterface $address
     * @param int|null $customerId Cart belongs to
     * @return void
     * @throws \Magento\Framework\Exception\InputException The specified address belongs to another customer.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function doValidateCustom(AddressInterface $address, ?int $customerId): void
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->create('Magento\Checkout\Model\Session');
        $state = $objectManager->get('Magento\Framework\App\State');
        
        // Adding this Validation skip for Recurring Order Generation from as validator considers it as Guest User & Existing address cannot belong to a guest
        if ($checkoutSession->getIgnoreCronValidation() && $state->getAreaCode() == 'crontab') {
            return;
        }
        
        $org_id = $this->customerSession->getOrganization();
        
        if ($org_id != "" && $org_id != "Myself") {
            return;
        }
        //validate customer id
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if (!$customer->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer id %1', $customerId)
                );
            }
        }

        if ($address->getCustomerAddressId()) {
            //Existing address cannot belong to a guest
            if (!$customerId) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
                );
            }
            //Validating address ID
            try {
                $this->addressRepository->getById($address->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid address id %1', $address->getId())
                );
            }
            
            $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $customerAddress = []; 
            foreach ($customerObj->getAddresses() as $addressItems)
            {
                $customerAddress[] = $addressItems->toArray();
            }
            //Finding available customer's addresses
            $applicableAddressIds = array_map(function ($address) {
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                return $address['entity_id'];
            }, $customerAddress);
            if (!in_array($address->getCustomerAddressId(), $applicableAddressIds)) {
                $this->logger->critical(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
                );
            }
        }
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData The address data object.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The specified address belongs to another customer.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validate(AddressInterface $addressData)
    {
        $this->doValidateCustom($addressData, $addressData->getCustomerId());

        return true;
    }

    /**
     * Validate address to be used for cart.
     *
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return void
     * @throws \Magento\Framework\Exception\InputException The specified address belongs to another customer.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validateForCart(CartInterface $cart, AddressInterface $address): void
    {
        $this->doValidateCustom($address, $cart->getCustomerIsGuest() ? null : $cart->getCustomer()->getId());
    }
}
