<?php
/**
 * Filename     : Status.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Ui\Component\Listing\Column;

class InvoicingStatus implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray() {
        return [
            ['value' => 0, 'label' => __('Not Applied')],
            ['value' => 1, 'label' => __('Approved')],
            ['value' => 2, 'label' => __('Pending')],
            ['value' => 3, 'label' => __('Declined')],
            ['value' => 4, 'label' => __('On Hold')]
        ];
    }

}