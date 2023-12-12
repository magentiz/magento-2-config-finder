<?php
/**
 * Created by PhpStorm.
 * User: trungpham
 * Date: 28/07/2020
 * Time: 10:39
 */

namespace Magentiz\ConfigFinder\Block\Widget;


/**
 * Class Input
 * @package Magentiz\ConfigFinder\Block\Widget
 */
class Input extends \Magento\Backend\Block\Widget
{
    protected function _construct()
    {
        $this->setTemplate('Magentiz_ConfigFinder::widget/input.phtml');
        parent::_construct();
    }

    /**
     * @return mixed
     */
    public function getOnChange()
    {
        return $this->getData('on_change') ?: $this->getData('onchange');
    }

    /**
     * @return mixed
     */
    public function getOnKeyUp()
    {
        return $this->getData('on_keyup') ?: $this->getData('onkeyup');
    }

    /**
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $classes = [];
        $classes[] = 'input-text admin__control-text';
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->_attributesToHtml($this->_prepareAttributes($classes, $disabled));
    }

    /**
     * @param $classes
     * @param $disabled
     * @return array
     */
    protected function _prepareAttributes($classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'class' => join(' ', $classes),
            'onchange' => $this->getOnChange(),
            'onkeyup' => $this->getOnKeyUp(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
            'placeholder' =>  $this->getPlaceholder(),
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }

    /**
     * @param $attributes
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
