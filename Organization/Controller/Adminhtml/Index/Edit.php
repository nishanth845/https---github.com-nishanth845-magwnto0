<?php
/**
 * Filename     : Edit.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Aha\Organization\Model\Organization $organizationModel
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aha\Organization\Model\Organization $organizationModel,
        \Magento\Framework\Registry $coreRegistry,
        \Aha\Organization\Model\Address $orgAddress
    ) {
        $this->organizationModel = $organizationModel;
        $this->_coreRegistry = $coreRegistry;
        $this->orgAddress = $orgAddress;
        parent::__construct($context);
    }

    public function execute() {
        $id = $this->getRequest()->getParam('id');
        $model = $this->organizationModel;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Organization no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            } else {
                $orgAddressCollection = $this->orgAddress->getCollection()
                        ->addFieldToFilter('org_id', $id);
                if($orgAddressCollection->getSize() == 0){
                    $this->messageManager->addWarning(__('Address Field is Mandatory in order to proceed.'));
                }
            }
            $this->_coreRegistry->register('id', $model->getId());
        }
        
        $this->_coreRegistry->register('organization', $model);

        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->prepend((__('Edit Organization')));
        return $result;
    }
}