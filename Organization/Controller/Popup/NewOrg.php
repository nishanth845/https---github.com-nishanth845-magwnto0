<?php
/**
 * 
 * @category  AHA
 * @package   Organization
 * @author    alexander.rajan@laerdal.com
 **/
namespace Aha\Organization\Controller\Popup;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

class NewOrg extends Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function execute() {
        if($this->customerSession->isLoggedIn()) {
            $this->customerSession->unsTempOrgId();
            //$this->customerSession->unsOrgData();
            $layout = $this->_view->getLayout();
            $block = $layout->createBlock(\Aha\Organization\Block\Popup::class);
            $block->setTemplate('Aha_Organization::account/new_org.phtml');
            $this->getResponse()->setBody($block->toHtml());
        }
    }

}