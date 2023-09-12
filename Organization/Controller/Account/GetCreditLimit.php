<?php
namespace Aha\Organization\Controller\Account;

use Aha\Organization\Controller\Organization;
use Magento\Framework\Controller\ResultFactory;

class GetCreditLimit extends Organization
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aha\Organization\Logger\Logger $logger,
        \Aha\Forestna\Helper\Data $forentnaHelper
    ) {
        $this->forentnaHelper = $forentnaHelper;
        parent::__construct($context, $customerSession, $resultPageFactory, $logger);
    }

    public function execute() 
    {
        $resultData = array();
        $resultData['status'] = true;
        $resultData['credit_limit'] = "";
        //print_r($this->getRequest()->getParams());die;
        $id = $this->getRequest()->getParam('id');
        $forestId = $this->getRequest()->getParam('forestna_id');

        if(!empty($id) && !empty($forestId)){
            $resultData['credit_limit'] = $this->forentnaHelper->getCreditLimit($id,$forestId);
        }
        if (empty($resultData['credit_limit'])) {
            $resultData['status'] = false;
            $resultData['message'] = "Someting went wrong while trying to get the credit limit.";
        }
        
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultData);
        return $resultJson;
    }
}
