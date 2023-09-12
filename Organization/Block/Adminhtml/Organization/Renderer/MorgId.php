<?php
/*
 * @module Aha_Organization
 * @author Pallavi Sinha<pallavi.sinha@laerdal.com>
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Renderer;
 use Magento\Framework\DataObject;
/**
 * Type Class for render the subscription in the grid
 */
class MorgId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $row) 
    {
        $morgIdLink = '';
        if($row->getData($this->getColumn()->getIndex())) {
            $url = $this->getUrl('organization/index/edit/id/'.$row->getData('entity_id'));
            $morgIdLink='<a href="'.$url.'">'.$row->getData($this->getColumn()->getIndex()).'</a>';
        } 
        return $morgIdLink;
    }

}