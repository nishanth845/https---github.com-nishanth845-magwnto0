<?php
namespace Aha\Organization\Ui;

class MassAction extends \Magento\Ui\Component\MassAction
{
    private $authorization;
    
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        $components,
        array $data
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();

        $allowedActions = [];
        
        foreach ($config['actions'] as $action) {

            if (!$this->authorization->isAllowed('Aha_Organization::deleteAction') && $action['type'] == 'delete') {
                continue;
            }
            if (!$this->authorization->isAllowed('Aha_Organization::changeInvoice') && $action['type'] == 'po_request') {
                continue;
            }
            
            $allowedActions[] = $action;
        }

        $config['actions'] = $allowedActions;
        $this->setData('config', (array) $config);
    }
}
