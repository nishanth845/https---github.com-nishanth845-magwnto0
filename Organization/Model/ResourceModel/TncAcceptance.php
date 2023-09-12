<?php
/*
 * Filename     : TncAcceptance.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */

namespace Aha\Organization\Model\ResourceModel;

class TncAcceptance extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('aha_tnc_acceptance', 'entity_id');
    }

}
