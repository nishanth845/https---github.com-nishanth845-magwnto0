<?php
/*
 * Filename     : CustomersGrid.php
 * Developer    : Alexander
 * Company      : Aha
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;


class OrganizationGrid extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('customer.edit.tab.organization')
                     ->setInBanner($this->getRequest()->getPost('index_organizarion', null));

        return $resultLayout;
    }
}
