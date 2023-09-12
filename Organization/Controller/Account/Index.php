<?php
/**
 *
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class Index extends Organization
{
    /**
     * View Organization action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Organizations'));
    	return $resultPage;
    }
}
