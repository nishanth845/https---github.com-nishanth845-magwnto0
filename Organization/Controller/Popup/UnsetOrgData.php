<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Controller\Organization;

class UnsetOrgData extends Organization
{
    public function execute() {
        $this->customerSession->unsOrgData();
    }

}