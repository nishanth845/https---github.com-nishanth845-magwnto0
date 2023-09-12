<?php

namespace Aha\Organization\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{     
    
    protected $customerSetupFactory;      
    
    private $attributeSetFactory;
   
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
   
      
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {        
        // this file is requied to save "uid" attribute in customer and save its value in customer_entity table
        
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
          
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer_address');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();          
        
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
          
        $customerSetup->addAttribute("customer_address", "org_id", [
            'type' => 'static', // this is important to save in customer_entity table
            'label' => 'ORG ID',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => false,
            'position' => 99,
            'system' => 0,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
        ]);
          
        $attribute = $customerSetup->getEavConfig()->getAttribute("customer_address", "org_id")
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => [],
        ]);
          
        $attribute->save();
       
    }
}
