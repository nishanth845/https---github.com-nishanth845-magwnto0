<?php

namespace Aha\Organization\Ui\Component\Listing\Column;

class Store implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(\Aha\Organization\Helper\Data $helper)
    {
        $this->helper = $helper;
    }
    
    public function toOptionArray() {
        $stores = $this->helper->getStores();
        $options = [];
        foreach($stores as $key=>$name)
        {
            $options[]=['value' => $key , 'label' => $name];
        }
        return $options;
    }

}
