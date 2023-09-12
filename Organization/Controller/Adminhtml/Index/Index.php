<?php
/**
 * Filename     : Index.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{

    public function execute() {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->prepend((__('Organization')));
        return $result;
    }

}