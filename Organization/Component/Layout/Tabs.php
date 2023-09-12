<?php

namespace Aha\Organization\Component\Layout;

use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class Tabs
 */
class Tabs extends \Magento\Ui\Component\Layout\Tabs
{
    /**
     * Constructor
     *
     * @param UiComponentFactory $uiComponentFactory
     * @param null|string $navContainerName
     * @param array $data
     */
    public function __construct(
        UiComponentFactory $uiComponentFactory, 
        \Magento\Framework\AuthorizationInterface $authorization, $navContainerName = null,
        $data = [])
    {
        $this->authorization = $authorization;
        parent::__construct($uiComponentFactory, $navContainerName, $data);
    }

    /**
     * Add children data
     *
     * @param array $topNode
     * @param UiComponentInterface $component
     * @param string $componentType
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addChildren(array &$topNode, UiComponentInterface $component, $componentType)
    {
        $childrenAreas = [];
        $collectedComponents = [];

        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof DataSourceInterface) {
                continue;
            }
            if ($childComponent instanceof BlockWrapperInterface) {
                $this->addWrappedBlock($childComponent, $childrenAreas);
                continue;
            }
            if ($childComponent instanceof ComponentVisibilityInterface && !$childComponent->isComponentVisible()) {
                continue;
            }

            $name = $childComponent->getName();
            $config = $childComponent->getData('config');
            $collectedComponents[$name] = true;

            [$childComponent, $structure] = $this->buildChildComponentStructure($config, $childComponent);

            $tabComponent = $this->createTabComponent($childComponent, $name);

            if (isset($structure[$name]['dataScope']) && $structure[$name]['dataScope']) {
                $dataScope = $structure[$name]['dataScope'];
                unset($structure[$name]['dataScope']);
            } else {
                $dataScope = 'data.' . $name;
            }

            $childrenAreas[$name] = [
                'type' => $tabComponent->getComponentName(),
                'dataScope' => $dataScope,
                'config' => $config,
                'insertTo' => [
                    $this->namespace . '.sections' => [
                        'position' => $this->getNextSortIncrement()
                    ]
                ],
                'children' => $structure,
            ];
        }

        $this->structure[static::AREAS_KEY]['children'] = $childrenAreas;
        $topNode = $this->structure;
    }

    /**
     * Build child components structure of the tab
     *
     * @param array $config
     * @param UiComponentInterface $childComponent
     * @return array
     */
    private function buildChildComponentStructure(array $config, $childComponent): array
    {
        $name = $childComponent->getName();
        if (isset($config['is_collection']) && $config['is_collection'] === true) {
            $label = $childComponent->getData('config/label');
            
            $this->component->getContext()->addComponentDefinition(
                'collection',
                [
                    'component' => 'Magento_Ui/js/form/components/collection',
                    'extends' => $this->namespace
                ]
            );
            /**
             * @var UiComponentInterface $childComponent
             * @var array $structure
             */
            [$childComponent, $structure] = $this->prepareChildComponents($childComponent, $name);

            $childrenStructure = $structure[$name]['children'];
            
            $deleteAddress = true;
            $addAddress = true;

            if($name == "address" && strtolower((string)$label) == "addresses" ) {
                if (!$this->authorization->isAllowed('Aha_Organization::delete_address')){
                    $deleteAddress = false;
                }

                if (!$this->authorization->isAllowed('Aha_Organization::add_address')){
                    $addAddress = false;
                }
            }
            // var_dump($deleteAddress);die;
            $structure[$name]['children'] = [
                $name . '_collection' => [
                    'type' => 'collection',
                    'config' => [
                        'active' => 1,
                        'enableDelete' => $deleteAddress,
                        'enableAdd' => $addAddress,
                        'removeLabel' => __('Remove %1', $label),
                        'addLabel' => __('Add New %1', $label),
                        'removeMessage' => $childComponent->getData('config/removeMessage'),
                        'itemTemplate' => 'item_template',
                    ],
                    'children' => [
                        'item_template' => ['type' => $this->namespace,
                            'isTemplate' => true,
                            'component' => 'Magento_Ui/js/form/components/collection/item',
                            'childType' => 'group',
                            'config' => [
                                'label' => __('New %1', $label),
                            ],
                            'children' => $childrenStructure
                        ]
                    ]
                ]
            ];
        } else {
            /**
             * @var UiComponentInterface $childComponent
             * @var array $structure
             */
            [$childComponent, $structure] = $this->prepareChildComponents($childComponent, $name);
        }

        return [$childComponent, $structure];
    }

}
