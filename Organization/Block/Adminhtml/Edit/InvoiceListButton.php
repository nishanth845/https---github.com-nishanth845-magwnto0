<?php
namespace Aha\Organization\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton
 */
class InvoiceListButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helperBackend;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Helper\Data $HelperBackend
    ) {
        $this->authorization = $authorization;
        $this->_coreRegistry = $registry;
        $this->_helperBackend = $HelperBackend;
        parent::__construct($context, $registry);
    }
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->_coreRegistry->registry('organization')->getId()) {
            if ($this->_coreRegistry->registry('organization')->getForestnaId()) {
                return $data = [
                    'label' => __('View Invoices'),
                    'class' => __('invoice-list-button'),
                    'data_attribute' => [
                        'url-list' => $this->getInvoiceListUrl(),
                        'url-pdf' => $this->getInvoicePdf(),
                        'customer-id' => $this->_coreRegistry->registry('organization')->getForestnaId(),
                        'increment-id' => $this->_coreRegistry->registry('organization')->getForestnaId(),
                    ],
                    'on_click' => '',
                    'id' => 'order-view-invoice-list-button',
                ];
            }
        }
    }

    public function getInvoiceListUrl()
    {
        return $this->_helperBackend->getUrl('Forest/getinvoicelist');
    }

    public function getInvoicePdf()
    {
        return $this->_helperBackend->getUrl('Forest/getinvoicepdf');
    }
}
