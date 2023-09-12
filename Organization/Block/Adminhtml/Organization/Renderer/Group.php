<?php
/*
 * Filename     : Group.php
 * Developer    : Alexander
 * Company      : AHa
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Renderer;
 
/**
 * Type Class for render the subscription in the grid
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * 
     * @param \Magento\Customer\Model\Group $customerGroup
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroup
    ) {
        $this->customerGroup = $customerGroup;
    }
    
    public function toOptionArray() 
    {
        $collection = $this->customerGroup->create()->setRealGroupsFilter()->toOptionArray();
        $customerGroup = array();
        foreach ($collection as $item) {
            $customerGroup[$item['value']] = $item['label'];
        }
        
        return $customerGroup;
    }

}