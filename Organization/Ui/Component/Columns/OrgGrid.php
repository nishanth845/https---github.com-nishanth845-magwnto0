<?php
namespace Aha\Organization\Ui\Component\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class OrgGrid extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helperBackend;

    /**
     * @var \Aha\Organization\Model\Organization
     */
    protected $_orgModel;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Helper\Data $HelperBackend,
        \Aha\Organization\Model\Organization $orgModel,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_helperBackend = $HelperBackend;
        $this->_orgModel = $orgModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $entity_id = $item['entity_id'];
                
                $fieldName = $this->getData('name');
                if ($entity_id) {
                    if(!$item['forestna_id']) {
                        continue;
                    }
                    $item[$fieldName . '_orderIncremId'] = $item['forestna_id'];
                    $item[$fieldName . '_customerId'] = $item['forestna_id'];
                    $item[$fieldName . '_orgId'] = $entity_id;
                    $item[$fieldName . '_invoiceGetUrl'] = $this->_helperBackend->getUrl('Forest/getinvoicelist');
                    $item[$fieldName . '_invoicePdfUrl'] = $this->_helperBackend->getUrl('Forest/getinvoicepdf');
                    $item[$fieldName . '_html'] = html_entity_decode("<button type='button' class='button_$entity_id'><span>View</span></button>");
                }
            }
        }
        return $dataSource;
    }

    public function getAttributeValue($order, $column)
    {
        $value = $order->getData($column);
        if (!empty($value)) {
            return $value;
        }
        return false;
    }
}
