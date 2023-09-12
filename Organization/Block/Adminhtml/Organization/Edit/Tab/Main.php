<?php
/*
 * Filename     : Main.php
 * Developer    : Alexander
 * Company      : AHA
 * Module       : Aha_Organization
 */
namespace Aha\Organization\Block\Adminhtml\Organization\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Aha\Organization\Block\Adminhtml\Organization\Renderer\Group $customerGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aha\Organization\Block\Adminhtml\Organization\Renderer\Group $customerGroup,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Aha\Organization\Helper\Data $helper,
        array $data = []
    ) {
        $this->customerGroup = $customerGroup;
        $this->authorization = $authorization;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    public function _prepareForm()
    {
        
        $model = $this->_coreRegistry->registry('organization');
        $flag = false;
        $storeFlag = false;
        $invoiceFlag = false;
        $creditLimitFlag = false;
        $cGroupFlag = false;
        $taxExemptFlag = false;
        $billingFlag = false;
        $billingCCFlag = false;
        $othersFlag = false;

        $dataForm = "aha_organization_form";    
        
        if (!$this->authorization->isAllowed('Aha_Organization::storeView')) {
                $storeFlag = true;
         }
        
        if ($model->getId()) {            
            
            
            if (!$this->authorization->isAllowed('Aha_Organization::invoice_status')) {
                $invoiceFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::allocated_credit_limit')) {
                $creditLimitFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::customer_group')) {
                $cGroupFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::tax_exemption')) {
                $taxExemptFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::billing_email')) {
                $billingFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::billing_cc')) {
                $billingCCFlag = true;
            }

            if (!$this->authorization->isAllowed('Aha_Organization::other_fields')) {
                $othersFlag = true;
                $flag = true;
            }

            $orgType = $model->getOrgType();
            
            if($orgType == 1){
                $flag = true;
            }
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => '']
        );
        
        $stores = $this->helper->getStores();
        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'store_id',
                'label' => __('Store'),
                'title' => __('Store'),
                'options' => $stores,
                'disabled' => $storeFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'id', 'data-form-part' => $dataForm]);
        

            $fieldset->addField(
                'morg_id',
                'text',
                [
                    'name' => 'morg_id',
                    'label' => __('Magento Org Id'),
                    'title' => __('Magento Org Id'),
                    'disabled' => true,
                    'data-form-part' => $dataForm
                ]
            );
        }
        
        if($flag){
            $fieldset->addField(
                'tc_id_number',
                'text',
                [
                    'name' => 'tc_id_number',
                    'label' => __('TC Id Number'),
                    'title' => __('TC Id Number'),
                    'required' => true,
                    'disabled' => $flag
                ]
            );
        }
            
        $fieldset->addField(
            'org_name',
            'text',
            [
                'name' => 'org_name',
                'label' => __('Org Name'),
                'title' => __('Org Name'),
                'required' => true,
                'disabled' => $flag,
                'data-form-part' => $dataForm
            ]
        );

        $fieldset->addField(
            'tax_number',
            'text',
            [
                'name' => 'tax_number',
                'label' => __('Tax ID or VAT number'),
                'title' => __('Tax ID or VAT number'),
                'note' => 'Note: Max 25 alphanumeric characters are allowed including (-,.,/)',
                'maxlength' => 25,
                'disabled' => $othersFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'org_status',
            'select',
            [
                'name' => 'org_status',
                'label' => __('Org Status'),
                'title' => __('Org Status'),
                'value' => 1,
                'options' => ['1' => __('Active'), '0' => __('In-active')],
                'disabled' => $flag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'po_request',
            'select',
            [
                'name' => 'po_request',
                'label' => __('Invoicing Status'),
                'title' => __('Invoicing Status'),
                'options' => \Aha\Organization\Helper\Data::INVOICING_STATUS,
                'disabled' => $invoiceFlag,
                'data-form-part' => $dataForm
            ]
        );

        $fieldset->addField(
            'allocated_credit_limit',
            'text',
            [
                'name' => 'allocated_credit_limit',
                'label' => __('Allocated Credit Limit'),
                'title' => __('Allocated Credit Limit'),
                'disabled' => $creditLimitFlag,
                'note' => 'Total approved line of credit. Numerical value with decimal. Ex: 20000.00',
                'class' => 'validate-number',
                'data-form-part' => $dataForm
            ]
        );

        $fieldset->addField(
            'customer_group',
            'select',
            [
                'name' => 'customer_group',
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'options' => $this->customerGroup->toOptionArray(),
                'disabled' => $cGroupFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'tnc_required',
            'select',
            [
                'name' => 'tnc_required',
                'label' => __('Is Terms Required'),
                'title' => __('Is Terms Required'),
                'value' => 1,
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'disabled' => $othersFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'etc',
            'select',
            [
                'name' => 'etc',
                'label' => __('ETC'),
                'title' => __('ETC'),
                'value' => '',
                'options' => ['0' => __('No'),'1' => __('Yes')],
                'disabled' => $othersFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'prepayment',
            'select',
            [
                'name' => 'prepayment',
                'label' => __('Prepayment'),
                'title' => __('Prepayment'),
                'value' => '',
                'options' => ['0' => __('No'),'1' => __('Yes')],
                'disabled' => $othersFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'tax_exemption',
            'select',
            [
                'name' => 'tax_exemption',
                'label' => __('Tax Exemption'),
                'title' => __('Tax Exemption'),
                'value' => '',
                'options' => [ '0' => __('No'),'1' => __('Yes')],
                'disabled' => $taxExemptFlag,
                'data-form-part' => $dataForm
            ]
        );
        
        $fieldset->addField(
            'billing_email',
            'text',
            [
                'name' => 'billing_email',
                'label' => __('Billing Email Address'),
                'title' => __('Billing Email Address'),
                'data-form-part' => $dataForm,
                'required' => true,
                'disabled' => $billingFlag,
                'class' => 'validate-email'
            ]
        );
        
        $fieldset->addField(
            'billing_email_cc',
            'text',
            [
                'name' => 'billing_email_cc',
                'label' => __('Billing Email CC'),
                'title' => __('Billing Email CC'),
                'data-form-part' => $dataForm,
                'disabled' => $billingCCFlag,
                'class' => 'validate-email'
            ]
        );
        
        $this->_eventManager->dispatch('adminhtml_organization_edit_tab_main_prepare_form', ['form' => $form]);

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General Information');
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
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
