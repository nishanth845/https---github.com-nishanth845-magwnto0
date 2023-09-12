<?php
/**
 * Filename     : Status.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Ui\Component\Listing\Column;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray() {
        return [
            ['value' => 0, 'label' => __('In-active')],
            ['value' => 1, 'label' => __('Active')]
        ];
    }

}