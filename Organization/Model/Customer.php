<?php
/*
 * Filename     : Organization.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct() {
        $this->_init(ResourceModel\Customer::class);
    }
    
}