<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Model\ResourceModel\OrgAdmin;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    protected function _construct() {
        $this->_init(\Aha\Organization\Model\OrgAdmin::class, \Aha\Organization\Model\ResourceModel\OrgAdmin::class);
    }
}