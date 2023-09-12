<?php
/*
 * Filename     : Customers.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Edit\Tab;

class Customers extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $customerCollectionFactory;

    private $orgCustomerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Aha\Organization\Model\OrganizationCustomerFactory $orgCustomerFactory
     * @param \Aha\Organization\Block\Adminhtml\Organization\Renderer\Group $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Aha\Organization\Model\OrganizationCustomerFactory $orgCustomerFactory,
        \Aha\Organization\Block\Adminhtml\Organization\Renderer\Group $customerGroup,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->orgCustomerFactory = $orgCustomerFactory;
        $this->customerGroup = $customerGroup;
        $this->authorization = $authorization;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('customersGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        //$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if (!empty($this->getSelectedCustomers())) {
            $this->setDefaultFilter(['in_customer' => 1]);
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_customer') {
            $customerIds = $this->getSelectedCustomers();

            if (empty($customerIds)) {
                $customerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $customerIds]);
            } else {
                if ($customerIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $customerIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    public function _prepareCollection()
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect('firstname');
        $collection->addAttributeToSelect('lastname');
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('group_id');
        if (!$this->authorization->isAllowed('Aha_Organization::orgCheckbox')) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->getSelectedCustomers()));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {
        if ($this->authorization->isAllowed('Aha_Organization::orgCheckbox')) {
        $this->addColumn(
            'in_customer',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_customer',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->getSelectedCustomers(),
                'data-form-part' => 'aha_organization_form'
            ]
        );
        }

        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index' => 'firstname',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'index' => 'lastname',
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
//        $this->addColumn(
//            'group_id',
//            [
//                'type' => 'options',
//                'header' => __('Customer Group'),
//                'index' => 'group_id',
//                'width' => '50px',
//                'options' => $this->customerGroup->toOptionArray(),
//                'sortable'  => false,
//                'filter'  => false
//            ]
//        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/customer/grid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
    
    public function _getSelectedCustomers()
    {
        return $this->orgCustomerFactory->create()
                ->getCollection()
                ->addFieldToFilter('parent_id', $this->getRequest()->getParam('id'))
                ->addFieldToFilter('is_admin', 1);
    }
    
    public function getSelectedCustomers()
    {
        $orgCustomerCollection = $this->_getSelectedCustomers();

        $orgCostomerIds = array();
        foreach ($orgCustomerCollection as $customer){
            $orgCostomerIds[] = $customer->getCustomerId();
        }
        
        if (!is_array($orgCostomerIds)) {
            $orgCostomerIds = [];
        }
        
        return $orgCostomerIds;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
