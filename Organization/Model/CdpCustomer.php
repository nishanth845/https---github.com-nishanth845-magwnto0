<?php
/**
 * Filename     : OrganizationCustomer.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model;

class CdpCustomer extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct() {
        $this->_init(ResourceModel\CdpCustomer::class);
    }
    
}