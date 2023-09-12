<?php
/**
 * Filename     : CustomersTab.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

/**
 * Class CustomerOrdersTab
 *
 * @package Magento\Sales\Block\Adminhtml
 */
class CdpTab extends TabWrapper
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = true;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context, 
        Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->authorization = $authorization;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        if (!$this->authorization->isAllowed('Aha_Organization::cdp')) {
            return false;
        }
        
        if($this->coreRegistry->registry('id')){
            return true;
        }
        
        return false;
    }

    /**
     * Return Tab label
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Purchase Codes');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/cdp/index', ['_current' => true]);
    }
}
