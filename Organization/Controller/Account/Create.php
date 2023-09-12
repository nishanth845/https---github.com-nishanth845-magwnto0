<?php
/*
 * @category    AHA
 * @package     Aha_Organization
 * @author      alexander.rajan@laerdal.com
 *
 */
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;

class Create extends Organization
{
    /**
     * View Create Organization action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Create an Account'));
        return $resultPage;
    }

}
