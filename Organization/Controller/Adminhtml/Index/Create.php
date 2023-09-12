<?php
/**
 * Filename     : Create.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Create extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aha\Organization\Model\Organization $organizationModel,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->organizationModel = $organizationModel;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute() {
        $model = $this->organizationModel;
        $this->_coreRegistry->register('organization', $model);
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $result->getConfig()->getTitle()->prepend((__('New Organization')));
        return $result;
    }
}