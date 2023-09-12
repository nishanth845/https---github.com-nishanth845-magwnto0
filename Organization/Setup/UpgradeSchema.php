<?php

namespace Aha\Organization\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
    

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        // this file is requied to add "org_id" field in customer_address_entity table 
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->oneZeroOne($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->oneOneZero($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->oneOneOne($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->oneOneTwo($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $this->oneOneThree($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $this->oneOneFour($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $this->oneOneFive($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            $this->oneOneSix($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            $this->oneOneSeven($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $this->oneOneEight($setup);
        }
        if (version_compare($context->getVersion(), '1.1.9', '<')) {
            $this->oneOneNine($setup);
        }
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->oneTwoZero($setup);
        }
        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->oneTwoOne($setup);
        }
        $setup->endSetup();
    }

    private function oneZeroOne($setup)
    {
        // Get module table
        $tableName = $setup->getTable('customer_address_entity');

        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            
            $setup->getConnection()->addColumn(
                $tableName,
                'org_id', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Organization ID'
                ]
            );

            $setup->getConnection()->addIndex($tableName, $setup->getIdxName('customer_address_entity', ['org_id']), ['org_id']);
        }
    }
    
    private function oneOneZero($setup) 
    {
        //alter table of aha_organization
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'etc', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'ETC'
                ]
            );
        $setup->getConnection()->addColumn(
                $tableName,
                'morg_id', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Magento Org ID'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'sub_org', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Sub Org'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'po_request', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'PO Request'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'has_cdp', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Has CDP'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'has_multiple_cdp',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Has Multiple CDP'
                ]
            );
        
        //alter table of aha_organization_customer
        $tableName = $setup->getTable('aha_organization_customer');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'is_admin', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Admin'
                ]
            );
        
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'is_request', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Request'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'role', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Role'
                ]
            );
        
        //create aha_cdp table for CDP training centrals acc
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aha_cdp')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'cdp_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true, 'default' => null],
            'Name'
        )->addColumn(
            'purchase_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true, 'default' => null],
            'Purchase Code'
        )->addColumn(
            'admin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null],
            'Admin'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->setComment(
            'AHA CDP'
        );
        $setup->getConnection()->createTable($table);

        //create aha_org_cdp_customer table for mapping cdp with organization
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aha_org_cdp_customer')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'tc_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'TC ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'cdp_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'CDP ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $setup->getIdxName('aha_org_cdp_customer', ['tc_id']),
            ['tc_id']
        )->addIndex(
            $setup->getIdxName('aha_org_cdp_customer', ['cdp_id']),
            ['cdp_id']
        )->addIndex(
            $setup->getIdxName('aha_org_cdp_customer', ['customer_id']),
            ['customer_id']
        )->addForeignKey(
            $setup->getFkName('aha_org_cdp_customer', 'tc_id', 'aha_organization', 'entity_id'),
            'tc_id',
            $setup->getTable('aha_organization'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aha_org_cdp_customer', 'cdp_id', 'aha_cdp', 'entity_id'),
            'cdp_id',
            $setup->getTable('aha_cdp'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aha_org_cdp_customer', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'AHA CDP Mapping'
        );
        $setup->getConnection()->createTable($table);

        //create aha_org_admin_req table
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aha_org_admin_req')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'tc_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            50,
            ['unsigned' => true],
            'TC ID'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null],
            'Email'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'CDP ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->setComment(
            'AHA Organization Admin request'
        );
        $setup->getConnection()->createTable($table);
        
        //removing existing column
        $setup->getConnection()->dropIndex(
            $setup->getTable('aha_organization'),
            $setup->getIdxName('aha_organization', ['org_id'])
        );
        $setup->getConnection()->dropIndex(
            $setup->getTable('aha_organization'), 
            $setup->getIdxName('aha_organization',['org_id', 'tc_id'])
        );
        $setup->getConnection()->dropColumn($setup->getTable('aha_organization'), 'org_id');
        $setup->getConnection()->dropColumn($setup->getTable('aha_organization'), 'purchase_code');
    }
    
    private function oneOneOne($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'prepayment', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Pre Payment'
                ]
            );
        $setup->getConnection()->addColumn(
                $tableName,
                'tax_exemption', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Tax Exemption'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'tax_number', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Tax Number'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $setup->getTable('aha_org_admin_req'),
                'u_name', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Name of User'
                ]
            );
    }
    
    private function oneOneTwo($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'billing_to', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Billing To'
                ]
            );
        $setup->getConnection()->addColumn(
                $tableName,
                'billing_dept', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Billing Department'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'billing_email', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Billing Email'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'forestna_id', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'ForestNA ID'
                ]
            );
        
        $setup->getConnection()->addColumn(
                $tableName,
                'credit_limit', 
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Credit Limit'
                ]
            );
    }
    
    private function oneOneThree($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'itc_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'ITC Code'
                ]
            );
    }
    
    private function oneOneFour ($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        
        $setup->getConnection()->dropIndex(
            $tableName,
            $setup->getIdxName('aha_organization', ['org_name'])
        );

        $setup->getConnection()->addIndex(
            $tableName, //table name
            'org_name_tc_id_number', // index name
            ['org_name','tc_id_number'], // field name
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
        );
        
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'has_address',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => 1,
                    'comment' => 'Has Address'
                ]
            );
        
    }
    
    private function oneOneFive ($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'applied_tax_exempt',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Applied Tax Excemption'
                ]
            );
        
    }
    
    private function oneOneSix ($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
        ->changeColumn(
            $tableName,
            'credit_limit',
            'credit_limit',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                'nullable' => true,
                'default' => null,
                'unsigned' => true,
                'comment' => 'Credit Limit'
            ]
            );
        
    }
    
    private function oneOneSeven ($setup)
    {
        $tableName = $setup->getTable('aha_organization');

        $setup->getConnection()
            ->addColumn(
                $tableName,
                'allocated_credit_limit',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Allocated Credit Limit'
                ]
            );
    }
    
    private function oneOneEight ($setup)
    {
        $tableName = $setup->getTable('aha_organization');

        $setup->getConnection()
            ->addColumn(
                $tableName,
                'billing_email_cc',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Billing Email CC'
                ]
            );
    }
    private function oneOneNine($setup)
    {
        // Get module table
        $tableName = $setup->getTable('sales_order_grid');

        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $tableName,
                'org_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Organization ID'
                ]
            );
        }
    }
    
    private function oneTwoZero($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'unsigned' => true,
                    'comment' => 'Store View ID'
                ]
            );
    }

    private function oneTwoOne($setup)
    {
        $tableName = $setup->getTable('aha_organization');
        $setup->getConnection()
            ->addColumn(
                $tableName,
                'website_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'unsigned' => true,
                    'comment' => 'Website ID'
                ]
            );
    }

}
