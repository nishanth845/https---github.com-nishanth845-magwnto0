<?php
namespace Aha\Organization\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class SubDisallowAddToCartMessageBlock
 * @package Aha\Subscription\Setup\Patch\Data
 * @codeCoverageIgnore
 */
class PcNote implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * AddNewCmsPage constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $blockData = [
            'title' => 'Checkout PC Note',
            'identifier' => 'checkout_pc_note',
            'content' => '<p class="m-0">The products below will be automatically fulfilled to your Intelligo License Manager. Please confirm or enter your License Manager Purchase Code. If you do not have a License Manager Purchase Code please call <a class="font-red" href="{{store url="intelligo-contact"}}" target="_blank">Customer Support</a>.</p>',
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        $this->moduleDataSetup->startSetup();
        /* Save CMS Block logic */
        $this->blockFactory->create()->setData($blockData)->save();
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
