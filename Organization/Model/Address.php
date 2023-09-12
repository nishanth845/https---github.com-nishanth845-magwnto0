<?php
/*
 * Filename     : Organization.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model;

class Address extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct() {
        $this->_init(ResourceModel\Address::class);
    }
    
}