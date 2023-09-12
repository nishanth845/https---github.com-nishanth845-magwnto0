<?php
/*
 * Filename     : MassInvoicingStatus.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Aha\Organization\Model\ResourceModel\Organization\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassInvoicingStatus
 */
class MassInvoicingStatus extends \Magento\Backend\App\Action
{

    /**
     * 
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }
    
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $orgUpdated = 0;
        foreach ($collection as $organization) {
            
            $organization->setPoRequest($this->getRequest()->getParam('id'))->save();
            $orgUpdated++;
        }

        if ($orgUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $orgUpdated));
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

}
