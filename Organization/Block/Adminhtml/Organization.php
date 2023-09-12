<?php
namespace Aha\Organization\Block\Adminhtml;

class Organization extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info 
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Aha\Organization\Model\Organization $orgModel,
        array $data = array()
    ) {
        $this->orgModel = $orgModel;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getOrgData($orgId){
        return $this->orgModel->load($orgId);
    }
    
}

