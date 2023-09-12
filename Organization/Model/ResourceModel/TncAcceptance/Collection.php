<?php
/*
 * Filename     : Collection.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model\ResourceModel\TncAcceptance;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    protected function _construct() {
        $this->_init(\Aha\Organization\Model\TncAcceptance::class, \Aha\Organization\Model\ResourceModel\TncAcceptance::class);
    }
}