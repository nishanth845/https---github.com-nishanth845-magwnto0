<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Model\ResourceModel;

class Address extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('customer_address_entity', 'entity_id');
    }

}
