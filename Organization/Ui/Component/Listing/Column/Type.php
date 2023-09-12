<?php
/**
 * Filename     : Type.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Ui\Component\Listing\Column;

/**
 * Class Actions
 *
 * @api
 * @since 100.0.2
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray() {
        return [
            ['value' => 1, 'label' => __('TC')],
            ['value' => 2, 'label' => __('Non TC')]
        ];
    }

}
