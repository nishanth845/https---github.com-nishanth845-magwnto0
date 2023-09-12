<?php
/*
 * Filename     : OrganizationGrid.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Edit\Tab;

class OrganizationGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

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
     * @param \Laerdal\TrainingOrganization\Model\OrganizationCustomerFactory $orgCustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Aha\Organization\Block\Adminhtml\Organization\Renderer\Group $customerGroup,
        \Aha\Organization\Model\OrganizationFactory $orgCollectionFactory,
        \Aha\Organization\Model\OrganizationCustomerFactory $orgCustomerFactory,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerGroup = $customerGroup;
        $this->orgCollectionFactory = $orgCollectionFactory;
        $this->orgCustomerFactory = $orgCustomerFactory;
        $this->authorization = $authorization;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('organizationGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        //$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if (!empty($this->getSelectedOrganization())) {
            $this->setDefaultFilter(['in_organization' => 1]);
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_organization') {
            $orgIds = $this->getSelectedOrganization();

            if (empty($orgIds)) {
                $orgIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $orgIds]);
            } else {
                if ($orgIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $orgIds]);
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
        $collection = $this->orgCollectionFactory->create()->getCollection();
        
        if (!$this->authorization->isAllowed('Aha_Organization::orgCheckbox')) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->getSelectedOrganization()));
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
                'in_organization',
                [
                    'header_css_class' => 'a-center',
                    'type' => 'checkbox',
                    'name' => 'in_organization',
                    'align' => 'center',
                    'index' => 'entity_id',
                    'values' => $this->getSelectedOrganization(),
                ]
            );
        }

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'morg_id',
            [
                'header' => __('Magento Org ID'),
                'index' => 'morg_id',
                'class' => 'xxx',
                'width' => '50px',
                'renderer' => \Aha\Organization\Block\Adminhtml\Organization\Renderer\MorgId::class,
            ]
        );
        
        $this->addColumn(
            'tc_id_number',
            [
                'header' => __('TC ID Number'),
                'index' => 'tc_id_number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'org_name',
            [
                'header' => __('Name'),
                'index' => 'org_name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'org_status',
            [
                'type' => 'options',
                'header' => __('Org Status'),
                'index' => 'org_status',
                'class' => 'xxx',
                'width' => '50px',
                'options' => array(1 => "Active", 0 => "In-active"),
            ]
        );
//        $this->addColumn(
//            'customer_group',
//            [
//                'type' => 'options',
//                'header' => __('Customer Group'),
//                'index' => 'customer_group',
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
        return $this->getUrl('*/*/organizationgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
    
    public function _getSelectedOrganization()
    {
        return $this->orgCustomerFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))
                ->addFieldToFilter('is_admin', 1);
    }
    
    public function getSelectedOrganization()
    {
        $CustomerOrgCollection = $this->_getSelectedOrganization();

        $orgIds = array();
        foreach ($CustomerOrgCollection as $organization){
            $orgIds[] = $organization->getParentId();
        }
        
        if (!is_array($orgIds)) {
            $orgIds = [];
        }
        return $orgIds;
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
