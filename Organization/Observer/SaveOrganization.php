<?php
/**
 * Filename     : SaveOrganization.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrganization implements ObserverInterface
{
      
    /**
     * @param  \Aha\Organization\Helper\Data $helper
     */
    protected $_helper;
    
    protected $_request;
    
    /**
     * 
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Aha\Organization\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Aha\Organization\Helper\Data $helper
    ){ 
        $this->_request = $request;
        $this->_helper = $helper;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customer-save.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $CustomerData = $this->_request->getParams();
        $this->logger->info('Execute customer save observer');
        if(isset($CustomerData['organization'])){
            $this->logger->info('customer data : ',print_r($CustomerData['organization']));
            $this->_helper->MapOrganization($CustomerData);
        }
    }
}