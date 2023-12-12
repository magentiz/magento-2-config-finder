<?php
namespace Magentiz\ConfigFinder\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended;

/**
 * Class SelectExtend
 * @package Magentiz\ConfigFinder\Block\Widget\Grid\Column\Filter
 */
class SelectExtend extends Extended
{
    /**
     * @param AbstractFilter $filter
     * @return bool | mixed
     */
    public function isMultiple()
    {
        return $this->getColumn()->getData("multiple");
    }

    public function isSearch()
    {
        return (bool) $this->getColumn()->getData('search');
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $html = '';
        if ($this->isSearch()) {
            $id = $this->_getHtmlId();
            $placeHolder = __('Search username...');
            $html .= "<input type='text' id='search_{$id}' onkeyup='searchSelect(this, \"#{$this->_getHtmlId()} option\")' style='height: 2rem; margin-bottom: 0.5rem' placeholder='{$placeHolder}'/>";
        }
        $multiple = $this->isMultiple() ? "multiple" : "";
        $name = $this->isMultiple() ? $this->_getHtmlName() . '[]' : $this->_getHtmlName();
        $html .= '<select ' . $multiple . ' ' . $this->getCssProperty() . ' name="' . $name . '" id="' . $this->_getHtmlId() . '"' . $this->getUiId(
            'filter',
            $this->_getHtmlName()
        ) . 'class="no-changes admin__control-select">';
        $value = $this->getValue();
        foreach ($this->_getOptions() as $option) {
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * @param AbstractFilter $filter
     * @return string
     */
    public function getCssProperty()
    {
        $properties = ["width", "height"];
        $results = [];
        foreach ($properties as $prop) {
            $propValue = $this->getColumn()->getData("$prop");
            if ($propValue) {
                $results[] = "{$prop}:{$propValue};";
            }
        }
        if ($results) {
            return "style='" . implode("", $results) . "'";
        }
        return "";
    }

    /**
     * Render an option with selected value
     *
     * @param array $option
     * @param string $value
     * @return string
     */
    protected function _renderOption($option, $value)
    {
        if ($this->isMultiple()) {
            $value = $value ?: [];
            if (! is_array($value)) {
                $value = [$value];
            }
            $selected = in_array($option['value'], $value) && $value ? ' selected="selected"' : '';
        } else {
            $selected = $option['value'] == $value && $value !== null ? ' selected="selected"' : '';
        }
        return '<option value="' . $this->escapeHtml(
            $option['value']
        ) . '"' . $selected . '>' . $this->escapeHtml(
            $option['label']
        ) . '</option>';
    }
}
