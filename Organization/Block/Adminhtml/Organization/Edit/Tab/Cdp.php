<?php
/*
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Edit\Tab;

class Cdp extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const PC_SOURCE = array(
        'CDP' => 'CDP',
        'IPA' => 'Intelligo'
    );
    
    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Aha\Organization\Model\CdpCustomer $cdpCustomerCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Aha\Organization\Model\CdpCustomer $cdpCustomerCollection,
        array $data = []
    ) {
        $this->cdpCustomerCollection = $cdpCustomerCollection;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cdpGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->cdpCustomerCollection->getCollection();
        $collection->getSelect()
                ->join(
                    ['org' => $collection->getTable('aha_organization')],
                    'main_table.tc_id = org.entity_id',
                    ['tcid' => 'org.tc_id', 'org.tc_id_number']
                )->join(
                    ['cdp' => $collection->getTable('aha_cdp')],
                    'main_table.cdp_id = cdp.entity_id',
                    ['cdp.cdp_name', 'cdp.purchase_code', 'cdp.source']
                )->join(
                    ['customer' => $collection->getTable('customer_entity')],
                    'main_table.customer_id = customer.entity_id',
                    ['customer.email']
                )->where("main_table.tc_id = $id");

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'tc_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'tc_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'tc_id_number',
            [
                'header' => __('TC ID Number'),
                'index' => 'tc_id_number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'cdp_name',
            [
                'header' => __('Name'),
                'index' => 'cdp_name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'purchase_code',
            [
                'header' => __('Purchase Code'),
                'index' => 'purchase_code',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'source',
            [
                'header' => __('Source'),
                'index' => 'source',
                'class' => 'xxx',
                'width' => '50px',
                'type' => 'options',
                'options' => self::PC_SOURCE
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified row.
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/cdp/index', ['_current' => true]);
    }
}
