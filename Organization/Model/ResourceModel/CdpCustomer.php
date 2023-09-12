<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Model\ResourceModel;

class CdpCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('aha_org_cdp_customer', 'entity_id');
    }

}
