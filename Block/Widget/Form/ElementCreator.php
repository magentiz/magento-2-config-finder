<?php

namespace Magentiz\ConfigFinder\Block\Widget\Form;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ElementCreator
 * @package Magentiz\ConfigFinder\Block\Widget\Form
 */
class ElementCreator extends \Magento\Backend\Block\Widget\Form\Element\ElementCreator
{
    /**
     * @var array
     */
    private $modifiers;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * ElementCreator constructor.
     * @param TimezoneInterface $_localeDate
     * @param array             $modifiers
     */
    public function __construct(
        TimezoneInterface $_localeDate,
        array $modifiers = []
    ) {
        $this->_localeDate = $_localeDate;
        parent::__construct($modifiers);
    }

    /**
     * Creates element
     *
     * @param Fieldset  $fieldset
     * @param Attribute $attribute
     *
     * @return AbstractElement
     */
    public function create(Fieldset $fieldset, Attribute $attribute): AbstractElement
    {
        $config = $this->getElementConfig($attribute);

        if (!empty($config['rendererClass'])) {
            $fieldType = $config['inputType'] . '_' . $attribute->getAttributeCode();
            $fieldset->addType($fieldType, $config['rendererClass']);
        }

        return $fieldset
            ->addField($config['attribute_code'], $config['inputType'], $config)
            ->setEntityAttribute($attribute);
    }

    /**
     * Returns element config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function getElementConfig(Attribute $attribute): array
    {
        $defaultConfig = $this->createDefaultConfig($attribute);
        $config = $this->modifyConfig($defaultConfig);

        $config['label'] = __($config['label']);

        return $config;
    }

    /**
     * Returns default config
     *
     * @param Attribute $attribute
     * @return array
     */
    private function createDefaultConfig(Attribute $attribute): array
    {

        $defaultConfig = [
            'inputType' => $attribute->getFrontend()->getInputType(),
            'rendererClass' => $attribute->getFrontend()->getInputRendererClass(),
            'attribute_code' => $attribute->getAttributeCode(),
            'name' => $attribute->getAttributeCode(),
            'label' => $attribute->getFrontend()->getLabel(),
            'class' => $attribute->getFrontend()->getClass(),
            'required' => $attribute->getIsRequired(),
            'note' => $attribute->getNote(),
        ];
        if ($attribute->getFrontend()->getInputType() == 'datetime') {
            $defaultConfig['inputType'] = 'date';
            $defaultConfig['date_format'] = $this->_localeDate->getDateFormatWithLongYear();
        }
        return $defaultConfig;
    }

    /**
     *  Modify config
     *
     * @param array $config
     * @return array
     */
    private function modifyConfig(array $config): array
    {
        if ($this->isModified($config['attribute_code'])) {
            return $this->applyModifier($config);
        }
        return $config;
    }

    /**
     * Returns bool if attribute need to modify
     *
     * @param string $attribute_code
     * @return bool
     */
    private function isModified($attribute_code): bool
    {
        return isset($this->modifiers[$attribute_code]);
    }

    /**
     * Apply modifier to config
     *
     * @param array $config
     * @return array
     */
    private function applyModifier(array $config): array
    {
        $modifiedConfig = $this->modifiers[$config['attribute_code']];
        foreach (array_keys($config) as $key) {
            if (isset($modifiedConfig[$key])) {
                $config[$key] = $modifiedConfig[$key];
            }
        }
        return $config;
    }
}
