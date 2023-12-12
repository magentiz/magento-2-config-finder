<?php
namespace Magentiz\ConfigFinder\Block\Widget;

/**
 * Select widget
 *
 */
class Select extends \Magento\Backend\Block\Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magentiz_ConfigFinder::widget/select.phtml');
        parent::_construct();
    }

    /**
     * Retrieve onclick handler
     *
     * @return null|string
     */
    public function getOnChange()
    {
        return $this->getData('on_change') ?: $this->getData('onchange');
    }

    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $classes = [];
        $classes[] = 'admin__control-select';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->_attributesToHtml($this->_prepareAttributes($classes, $disabled));
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepareAttributes($classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'class' => join(' ', $classes),
            'onchange' => $this->getOnChange(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }

    /**
    * get options
    *
    * @return []
    */
    public function _getOptions()
    {
        $emptyOption = ['value' => null, 'label' => ''];

        $optionGroups = $this->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getOptions();
        if (!empty($colOptions) && is_array($colOptions)) {
            $options = [$emptyOption];

            foreach ($colOptions as $key => $option) {
                if (is_array($option)) {
                    $options[] = $option;
                } else {
                    $options[] = ['value' => $key, 'label' => $option];
                }
            }
            return $options;
        }
        return [];
    }

    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    public function _renderOption($option)
    {
    	$value = $this->getValue();
        $selected = $option['value'] == $value && $value !== null ? ' selected="selected"' : '';
        return '<option value="' . $this->escapeHtml(
            $option['value']
        ) . '"' . $selected . '>' . $this->escapeHtml(
            $option['label']
        ) . '</option>';
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function _attributesToHtml($attributes)
    {
        $html = '';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtmlAttr($attributeValue, false) . '" ';
        }

        return $html;
    }
}
