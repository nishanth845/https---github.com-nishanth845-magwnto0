<?php
/**
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model;

class Cdp extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct() {
        $this->_init(ResourceModel\Cdp::class);
    }
    
}