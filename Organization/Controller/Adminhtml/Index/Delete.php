<?php
/*
 * Filename     : MassDelete.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Index;

use Aha\Organization\Model\Organization as Orgmodel;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Delete
 */
class Delete extends \Magento\Backend\App\Action
{
    public function __construct(Context $context,Orgmodel $orgmodel) {
        parent::__construct($context);
        $this->orgmodel = $orgmodel;
    }

    public function execute()
    { 
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('id');
        if (!empty($id)) {
            try {
                $collection = $this->orgmodel->load($id);
                $collection->setOrgStatus(0)->save();
                $this->messageManager->addSuccess(__($collection->getOrgName() . ' - was disabled.'));
                //$collection->delete();
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }

        return $resultRedirect->setPath('organization/index');
    }

}
