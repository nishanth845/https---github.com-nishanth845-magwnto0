<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model\ResourceModel\CdpCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct() {
        $this->_init(\Aha\Organization\Model\CdpCustomer::class, \Aha\Organization\Model\ResourceModel\CdpCustomer::class);
    }
}