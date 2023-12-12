<?php


namespace Magentiz\ConfigFinder\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class FieldArray extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = "Magentiz_ConfigFinder::system/config/form/field/array.phtml";
    /**
     * Rows cache
     *
     * @var array|null
     */
    private $_arrayChildRowsCache;
    /**
     * @param $columnName
     *
     * @return string
     */
    protected function _getChildCellInputElementName($columnName)
    {
        return $this->getElement()->getName() . '[<%- parent_id %>][child][<%- _id %>][' . $columnName . ']';
    }

    /**
     * @param $columnName
     *
     * @return string
     * @throws \Exception
     */
    public function renderChildCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->_getChildCellInputElementName($columnName);

        if ($column['renderer']) {
            return $column['renderer']->setInputName(
                $inputName
            )->setInputId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setColumnName(
                $columnName
            )->setColumn(
                $column
            )->toHtml();
        }

        return '<input type="text" id="' . $this->_getCellInputElementId(
                'child<%- _id %>',
                $columnName
            ) .
            '"' .
            ' name="' .
            $inputName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            ($column['size'] ? 'size="' .
                $column['size'] .
                '"' : '') .
            ' class="' .
            (isset($column['class'])
                ? $column['class']
                : 'input-text') . '"' . (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }

    /**
     * @return array|null
     */
    public function getArrayChildRows()
    {
        if (null !== $this->_arrayChildRowsCache) {
            return $this->_arrayChildRowsCache;
        }
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                if (isset($row['child']) && $row['child']) {
                    foreach ($row['child'] as $rowChildId => $rowChild) {
                        $rowColumnValues = [];
                        foreach ($rowChild as $key => $value) {
                            $rowChild[$key] = $value;
                            $rowColumnValues[$this->_getCellInputElementId($rowChildId, $key)] = $rowChild[$key];
                        }
                        $rowChild['_id'] = $rowChildId;
                        $rowChild['parent_id'] = $rowId;
                        $rowChild['column_values'] = $rowColumnValues;
                        $result[$rowChildId] = new \Magento\Framework\DataObject($rowChild);
                        $this->_prepareArrayRow($result[$rowChildId]);
                    }//end foreach
                }//end if
            }//end foreach
        }//end if
        $this->_arrayChildRowsCache = $result;
        return $this->_arrayChildRowsCache;
    }
}
