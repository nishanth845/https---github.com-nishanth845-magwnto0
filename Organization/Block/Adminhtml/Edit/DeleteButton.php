<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Aha\Organization\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context, 
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $registry);
    }
    /**
     * @return array
     */
    public function getButtonData()
    {
        $org = $this->registry->registry('organization');
        
        if (!$this->authorization->isAllowed('Aha_Organization::delete')) {
            return false;
        }
        
        if(!$org->getId()){
            return false;
        }
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'id' => 'customer-edit-delete-button',
            
            'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to delete this Organization?')
                    . '\', \'' . $this->getDeleteUrl($org->getId()) . '\')',
            
            'sort_order' => 20,
        ];
    }

    /**
     * @return string
     */
    public function getDeleteUrl($id)
    {
        return $this->getUrl('*/*/delete', ['id' => $id]);
    }
}
