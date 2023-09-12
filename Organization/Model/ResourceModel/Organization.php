<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Model\ResourceModel;

class Organization extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('aha_organization', 'entity_id');
    }

}
