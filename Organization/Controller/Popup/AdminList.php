<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Aha\Organization\Controller\Organization;

class AdminList extends Organization
{
    
    public function execute() {
        $layout = $this->_view->getLayout();
        $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
        $block->setTemplate('Aha_Organization::account/admin_list.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

}