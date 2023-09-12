<?php

namespace Aha\Organization\Helper;

use Aha\Organization\Model\OrganizationFactory;
use Aha\Organization\Model\OrganizationCustomerFactory;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Address extends AbstractHelper
{
      /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Quote\Model\ShippingAddressAssignment
     */
    private $shippingAddressAssignment;
    
    public function __construct(
        Context $context,
        OrganizationFactory $OrganizationFactory,
        OrganizationCustomerFactory $OrganizationCustomerFactory,
        \Aha\Organization\Model\Address $orgAddress,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Aha\Organization\Helper\Data $orgHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, 
        \Aha\Organization\Model\CdpCustomer $cdpCustomer
    ) {
        $this->_OrganizationFactory = $OrganizationFactory->create();
        $this->_OrganizationCustomerFactory = $OrganizationCustomerFactory->create();
        $this->orgAddress = $orgAddress;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->orgHelper = $orgHelper;
        $this->_cart = $cart;
        $this->cartHelper = $cartHelper;
        $this->cdpCustomer = $cdpCustomer;
        $this->_addressFactory = $addressFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->addressRepository = $addressRepository;

parent::__construct($context);
    }
    
    public function getOrgBillingAddress($orgData=null)
    {
        $billingAddress ='';
        if (!empty($orgData)) {
                $default_billing_org_id =$orgData->getDefaultBilling();
               //$billingAddress = $this->_addressFactory->create()->load($default_billing_org_id);
        }
        return $default_billing_org_id;
    }
   public function getOrgShippingAddress($orgData=null)
    {
        $address ='';
        if (!empty($orgData)) {
                $default_shipping_org_id =$orgData->getDefaultShipping();
            //  $shippingAddress =   $this->addressRepository->getById($default_shipping_org_id);
             $shippingAddress = $this->_addressFactory->create()->load($default_shipping_org_id);
        }
      return $shippingAddress;  
    } 
}
