<?php

namespace Magentiz\ConfigFinder\Block\Widget\Grid;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 *
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const FULLTEXT_MIN_WORD_LEN = 3;
    /**
     * @var string
     */
    protected $_template = 'Magentiz_ConfigFinder::widget/grid/extended.phtml';

    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_massactionBlockName = \Magentiz\ConfigFinder\Block\Widget\Grid\Massaction\Extended::class;

    /**
     * @var string
     */
    protected $barcodeFilterField;

    /**
     * @var string
     */
    protected $orderFilterField;

    /**
     * @var null
     */
    protected $personalizedIdFilterField = null;

    /**
     * @var string
     */
    protected $_keyReset = '0ZZ4ADX!000';

    /**
     * @var int|null
     */
    protected $_maxRowsSelected;

    /**
     * @var int|null
     */
    protected $_maxBarcodeScan = 20;

    /**
     * @var bool|int|null
     */
    protected $_bulkbarcodescan = false;

    /**
     * @var bool|int|null
     */
    protected $_bulkorderscan;

    /**
     * @var null
     */
    protected $checkFilterSort = null;

    /**
     * @var array
     */
    protected $columnTableJoin = [];
    /**
     * @var array
     */
    protected $barcodeWrong = [];
    protected $currentFilterBarcode = [];

    /**
     * @var array
     */
    protected $orderWrong = [];

    /**
     * @var bool
     */
    protected $isMassActionExport = false;

    /**
     * @return Collection
     */
    public function getFullCollection()
    {
        return $this->getCollection();
    }

    /**
     * @param $columnId
     * @return bool
     */
    public function checkHaveFilter($columnId = '')
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $data = $this->_backendHelper->prepareFilterString($filter);
        if(isset($data[$columnId])) {
            return true;
        }
        return false;
    }
    /**
     * @return bool|null
     */
    protected function checkFilterSort()
    {
        if ($this->checkFilterSort === null) {
            $this->checkFilterSort = false;
            $columnSort = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            if ($columnSort && $columnSort != $this->_defaultSort) {
                $columnTableJoin = $this->columnTableJoin;
                if (empty($columnTableJoin) || in_array($columnSort, $columnTableJoin)) {
                    $this->checkFilterSort = true;
                }
            }
            $filter = $this->getParam($this->getVarNameFilter(), null);
            $data = $this->_backendHelper->prepareFilterString($filter);
            $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
            foreach ($data as $key => $value) {
                if (is_array($value) && count($value) == 1 && isset($value['locale'])) {
                    continue;
                }
                if ($key == 'scan_barcode' || $key == 'scan_order' && $value == '0ZZ4ADX!000') {
                    continue;
                }
                if (!empty($this->columnTableJoin) && !in_array($key, $this->columnTableJoin)) {
                    continue;
                }
                $this->checkFilterSort = true;
                break;
            }
        }
        return $this->checkFilterSort;
    }
    /**
     * get additional javascrip
     *
     * @return string
     */
    public function getAdditionalJavaScript()
    {
        $additionalJs = "";

        $additionalJs .= $this->getJsObjectName() . '.bulkBarcodeFilter = function(wrapId) {
            if($$(wrapId).length) {
                $$(wrapId)[0].toggle();
                var marginTop = $$(".mass-select-wrap")[0].style.marginTop;
                if(marginTop)
                {
                    var px = marginTop.replace("px", "");
                    var height = $$(wrapId)[0].getHeight();
                    if($$(wrapId)[0].visible()){
                        var margin = parseInt(px) + parseInt(height);
                    }else{
                        var margin = parseInt(px) - parseInt(height);
                    }

                    $$(".mass-select-wrap")[0].style.marginTop =  margin + "px";
                }
            }
        };';

        $additionalJs .= $this->getJsObjectName() . '.setValueToFilter = function(columnId, value) {
    		var filterFieldId = "' . $this->getId() . '" + "_filter_" + columnId;
    		if($(filterFieldId)) {
    			$(filterFieldId).setValue(value);
    		}
    	};';
        /** filterData {columid: valueFilter}  */
        $additionalJs .= $this->getJsObjectName() . '.doFilterValues = function(filterData) {
    		for(id in filterData) {
    			if(filterData.hasOwnProperty(id)) {
    				this.setValueToFilter(id, filterData[id]);
    			}
    		}
    		this.doFilter();
    	};';

        if ($this->getMassactionBlock() && $this->getMassactionBlock()->isAvailable()) {
            $additionalJs .= $this->getMassactionBlock()->getJsObjectName() . '.applyValue = function(valueId) {
	    		var selectId = "' . $this->getMassactionBlock()->getHtmlId() . '" + "-select";
	    		if($(selectId)){
	    			$(selectId).setValue(valueId);
	    		}
	    		' . $this->getMassactionBlock()->getJsObjectName() . '.apply();
	    	};';
        }

        if ($this->_maxRowsSelected && $this->getMassactionBlock()->isAvailable()) {
            $additionalJs .= $this->getMassactionBlock()->getJsObjectName() . '.setCheckbox = function(checkbox) {
	    		var checkedValues = (this.checkedString) ? this.checkedString.split(",") : [];
                if(checkedValues.length >= ' . $this->_maxRowsSelected . ' && checkbox.checked){
                    checkbox.checked = false;
                    this.checkedString = varienStringArray.remove(checkbox.value, this.checkedString);
                    this.updateCount();
                    alert("Max " + ' . $this->_maxRowsSelected . ' + " items can be selectable.");
                    return;
                }
                if(checkbox.checked) {
                    this.checkedString = varienStringArray.add(checkbox.value, this.checkedString);
                } else {
                    this.checkedString = varienStringArray.remove(checkbox.value, this.checkedString);
                }
                this.updateCount();
	    	};';

            $additionalJs .= $this->getMassactionBlock()->getJsObjectName() . '.setCheckedValues = function(values) {
	    		var checkedValues = values.split(",");
                if(checkedValues.length > ' . $this->_maxRowsSelected . '){
                    var availableValues = checkedValues.slice(-' . $this->_maxRowsSelected . ');
                    this.checkedString = availableValues.join(",");
                    alert("Max " + ' . $this->_maxRowsSelected . ' + " items can be selectable.");
                } else {
                    this.checkedString = values;
                }
	    	};';
        }

        if ($this->barcodeFilterField && $this->getMassactionBlock()->isAvailable()) {
            $hasBulkBarcodeScan = $this->_bulkbarcodescan ? 1 : 0 ;
            $additionalJs .= $this->getJsObjectName() . '.bindReadBarcode = function(event, ele) {
                var value = $(ele).getValue();
                if(value) {
                    var key = event.which || event.keyCode;
                    if(key == Event.KEY_RETURN){
                        $(ele).setValue("");
                        var checkedValues = (this.massaction.checkedString) ? this.massaction.checkedString.split(",") : [];
                        var maxBarcodeScan = ' . $this->_maxBarcodeScan . ';
                        var hasBulkBarcodeScan = '. $hasBulkBarcodeScan .'
                        if(maxBarcodeScan && checkedValues.length > maxBarcodeScan && !hasBulkBarcodeScan){
                            alert("Only allow maximum ' . $this->_maxBarcodeScan . '  items barcode scan.");
                            return;
                        }
                        ' . $this->getJsObjectName() . '.setValueToFilter("scan_barcode", value);
                        ' . $this->getJsObjectName() . '.setValueToFilter("is_bulk_barcode_import", 0);
                        ' . $this->getJsObjectName() . '.doFilter();
                        var checkInternal = 1;
                        var messageError = $("messages")
                         if(messageError){
                            messageError.remove()
                         }
                        jQuery( document ).ajaxStop(function() {
                            if (checkInternal) {
                                ' . $this->getMassactionBlock()->getJsObjectName() . '.selectVisible();
                                checkInternal = 0;
                                 $(input_barcode).focus();
                                 $(input_barcode).select();
                            }
                        });
                    }
                }
            };';
            $additionalJs .= $this->getJsObjectName() . '.bindResetBarcode = function(event, ele) {
                ' . $this->getJsObjectName() . '.setValueToFilter("scan_barcode", "' . $this->_keyReset . '");
                ' . $this->getJsObjectName() . '.doFilter(function() {
                    ' . $this->getMassactionBlock()->getJsObjectName() . '.unselectAll();
                    $(input_barcode).focus();
                   $(input_barcode).select();
                });
            };';
            $additionalJs .= '
                var messageError = $("messages")
                var filterFieldId = "' . $this->getId() . '" + "_filter_scan_barcode";
                if(messageError && $(filterFieldId).getValue()){
                    ' . $this->getJsObjectName() . '.setValueToFilter("scan_barcode", "' . $this->_keyReset . '");
                    ' . $this->getJsObjectName() . '.doFilter(function() {
                        ' . $this->getMassactionBlock()->getJsObjectName() . '.unselectAll();
                    });
                }
                if ($(filterFieldId).getValue() && !messageError) {
                    ' . $this->getMassactionBlock()->getJsObjectName() . '.selectVisible();
                    $(input_barcode).focus();
                   $(input_barcode).select();
                }
           ';
        }
        return $additionalJs;
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if ($this->barcodeFilterField) {
           $this->addColumn(
               'scan_barcode',
               [
                   'header' => __('Barcode'),
                   'type' => 'text',
                   'not_export_to_csv' => true,
                   'column_css_class' => 'no-display',
                   'header_css_class' => 'no-display',
                   'filter_condition_callback' => [$this, 'barcodeFilter'],
                   'is_system' => true
               ]
           );
        }
        if ($this->_bulkbarcodescan) {
            $this->addColumn(
                'is_bulk_barcode_import',
                [
                    'header' => __('Bulk Barcode'),
                    'type' => 'text',
                    'not_export_to_csv' => true,
                    'column_css_class' => 'no-display',
                    'header_css_class' => 'no-display',
                    'filter_condition_callback' => [$this, 'bulkbarcodeFilter'],
                    'is_system' => true
                ]
            );
        }
        if ($this->_bulkorderscan) {
            $this->addColumn(
                'is_bulk_order_import',
                [
                    'header' => __('Bulk Order'),
                    'type' => 'text',
                    'not_export_to_csv' => true,
                    'column_css_class' => 'no-display',
                    'header_css_class' => 'no-display',
                    'filter_condition_callback' => [$this, 'bulkorderFilter'],
                    'is_system' => true
                ]
            );
        }
        if ($this->orderFilterField) {
            $this->addColumn(
                'scan_order',
                [
                    'header' => __('Order'),
                    'type' => 'text',
                    'not_export_to_csv' => true,
                    'column_css_class' => 'no-display',
                    'header_css_class' => 'no-display',
                    'filter_condition_callback' => [$this, 'orderFilter']
                ]
            );
        }
        return parent::_prepareColumns();
    }


    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareCollection()
    {

        if ($this->getCollection()) {
            if ($params = $this->getBarcodeParams()) {
                $column = $this->getColumn("scan_barcode");
                if ($column) {
                    if (!$this->getRequest()->has($this->getVarNameFilter())) {
                        $column->getFilter()->setValue(implode(",", $params));
                    }
                }
            } else if ($params = $this->getOrderParams()) {
                $column = $this->getColumn("scan_order");
                if ($column) {
                    if (!$this->getRequest()->has($this->getVarNameFilter())) {
                        $column->getFilter()->setValue(implode(",", $params));
                    }
                }
            }
        }
        return parent::_prepareCollection();
    }

    /**
     * @param        $label
     * @param        $column
     * @param        $value
     * @param string $class
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildButtonFilter($label, $column, $value, $class = 'scalable primary')
    {
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => $label,
                'onclick' => $this->getJsObjectName() . ".doFilterValues({ $column : $value })",
                'class' => $class,
            ]
        )->toHtml();
    }

    /**
     * @return bool
     */
    public function checkShowInputBarcode()
    {
        return (bool)$this->barcodeFilterField;
    }

    /**
     * @return bool
     */
    public function checkShowInputOrder()
    {
        return (bool)$this->orderFilterField;
    }

    /**
     * @return string
     */
    public function getAdditionalHtmlBeforeMainButton()
    {
        $html = '';
        $html .= $this->rendererInputBarcode();
        $html .= $this->rendererInputOrder();
        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rendererInputBarcode()
    {
        $html = '';
        if ($this->checkShowInputBarcode() && $this->getMassactionBlock()->isAvailable()) {
            $html .= $this->getLayout()->createBlock(
                \Magentiz\ConfigFinder\Block\Widget\Input::class
            )->setData(
                [
                    'id' => "input_barcode",
                    'name' => 'barcode_filter',
                    'class' => 'input_filter_barcode',
                    'placeholder' => "Barcode separator by ,",
                    'onkeyup' => $this->getJsObjectName() . '.bindReadBarcode(event, this)'
                ]
            )->toHtml();
            $html .= $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Reset Barcode'),
                    'class' => 'scalable primary reset_barcode',
                    'onclick' => $this->getJsObjectName() . '.bindResetBarcode(event, this)'
                ]
            )->toHtml();
        }
        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rendererInputOrder()
    {
        $html = '';
        if ($this->checkShowInputOrder() && $this->getMassactionBlock()->isAvailable()) {
            $html .= $this->getLayout()->createBlock(
                \Magentiz\ConfigFinder\Block\Widget\Input::class
            )->setData(
                [
                    'id' => "input_order",
                    'name' => 'order_filter',
                    'class' => 'input_filter_order',
                    'placeholder' => "Enter order",
                    'onkeyup' => $this->getJsObjectName() . '.bindReadOrder(event, this)'
                ]
            )->toHtml();
            $html .= $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Reset Order'),
                    'class' => 'scalable primary reset_order',
                    'onclick' => $this->getJsObjectName() . '.bindResetOrder(event, this)'
                ]
            )->toHtml();
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getJavascript()
    {
        $html = '';
        $html .= '<script>
        require([
            "jquery",
        ], function ($) {
            "use strict";
            function modifyMassSelectWrap() {
                let headerBottomHeight = $(".admin__data-grid-header-bottom").height();
                if (headerBottomHeight) {
                    $(".mass-select-wrap").css({ marginTop: headerBottomHeight + 50});
                }
            }
            modifyMassSelectWrap();
            $("body").on("massSelectWrapUpdated", function () {
                modifyMassSelectWrap();
            });
        });
        </script>';
        return $html;
    }

    /**
     * @param array $default
     * @return array|mixed
     */
    protected function getBarcodeParams($default = [])
    {
        $sessionParamName = $this->getId() . "_barcode_items";
        return $this->_backendSession->getData($sessionParamName) ?: $default;
    }

    /**
     * @param array $default
     * @return array|mixed
     */
    protected function getOrderParams($default = [])
    {
        $sessionParamName = $this->getId() . "_order_items";
        return $this->_backendSession->getData($sessionParamName) ?: $default;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function setBarcodeParams($params = null)
    {
        $sessionParamName = $this->getId() . "_barcode_items";
        if (!$params) {
            $this->_backendSession->unsetData($sessionParamName);
        }
        return $this->_backendSession->setData($sessionParamName, $params);
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function setOrderParams($params = null)
    {
        $sessionParamName = $this->getId() . "_order_items";
        if (!$params) {
            $this->_backendSession->unsetData($sessionParamName);
        }
        return $this->_backendSession->setData($sessionParamName, $params);
    }

    /**
     * @param $field
     * @return $this
     */
    public function setBarcodeFilterField($field)
    {
        $this->barcodeFilterField = $field;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function setOrderFilterField($field)
    {
        $this->orderFilterField = $field;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function setPersonalizedIdFilterField($field)
    {
        $this->personalizedIdFilterField = $field;
        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function bulkbarcodeFilter($collection, $column)
    {
        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function bulkorderFilter($collection, $column)
    {
        return $this;
    }
    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function barcodeFilter($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());
        if ($value == $this->_keyReset) {
            $this->setBarcodeParams();
            $column->getFilter()->setValue('');
            return $this;
        }
        $filterBarcodes = $this->getBarcodeParams();
        if ($this->checkHaveBulkBarcodeFilter()) {
            $filterBarcodes = [];
        }
        $barcodes = preg_split('/\s*,\s*/u', $value);
        foreach ($barcodes as $barcode) {
            if (!in_array($barcode, $filterBarcodes)) {
                $filterBarcodes[] = $barcode;
                // find & replace barcode have space character
                if (preg_match('/\s+/', $barcode)) {
                    $filterBarcodes[] = preg_replace('/\s+/', '', $barcode);
                }
            }
        }
        if (count($filterBarcodes) >= 1) {
            $listBarcode = [];
            $listBarcodeRaw = [];
            $listPersonalizedId = [];
            foreach ($filterBarcodes as $item) {
                $listBarcodeRaw[] = $item;
                $listBarcode[] = ['like' => '%' . $item . '%'];
            }
            if ($listBarcode) {
                // if (barcode has fulltext
                $col = clone $column;
                $filterIndex = explode('.', $this->barcodeFilterField);
                $col->setFilterIndex($this->barcodeFilterField);
                $col->setIndex(array_pop($filterIndex));
                $mainTable = $this->getTableField($collection, $col);
                $columns = $this->getFulltextIndexColumns($collection, $mainTable, $col);
                if ($columns) {
                    $columns = $this->addTableAliasToColumns($columns, $collection, $mainTable);
                    $fulltext = [];
                    foreach ($listBarcodeRaw as $barcode) {
                        $keyword = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $barcode);
                        $keyword = \Safe\preg_replace('/\s+/u', ' ', $keyword);
                        $fulltext[]  = '*' . preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $keyword) . '*';
                    }
                    if (!empty($fulltext)) {
                        $collection->getSelect()->where(
                            'MATCH(' . implode(',', $columns) . ') AGAINST(? IN BOOLEAN MODE)',
                            '(' . implode(')(', $fulltext) . ')'
                        );
                    }
                }
                $collection->addFieldToFilter($this->barcodeFilterField, $listBarcode);
            }
        }
        $cloneCollection = clone $collection;
        $this->currentFilterBarcode = array_values($filterBarcodes);
        if ($cloneCollection->getSize()) {
            $this->setBarcodeParams($filterBarcodes);
            $this->checkBarcodeWrong($cloneCollection, $filterBarcodes);
            $column->getFilter()->setValue(implode(",", $filterBarcodes));
        } else {
            $this->barcodeWrong = $filterBarcodes;
            $this->setBarcodeParams();
            $column->getFilter()->setValue($this->_keyReset);
        }
        return $this;
    }

    /**
     * we have rule when use import bulk barcode will reset all current scan barcode filter
     * @return bool
     */
    public function checkHaveBulkBarcodeFilter()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $data = $this->_backendHelper->prepareFilterString($filter);
        if(isset($data['is_bulk_barcode_import']) && $data['is_bulk_barcode_import']) {
            return true;
        }
        return false;
    }
    /**
     * @param $collection
     * @param $filterBarcodes
     * @return void
     */
    public function checkBarcodeWrong($collection, $filterBarcodes)
    {
        $collection->setPageSize(1000);
        $listBarcodeFound = $collection->getColumnValues('barcode');
        $this->barcodeWrong = array_values(array_diff($filterBarcodes, $listBarcodeFound));
    }
    /**
     * @param $collection
     * @param $filterBarcodes
     * @return void
     */
    public function checkOrderWrong($collection, $filterOrders)
    {
        $collection->setPageSize(1000);
        $listOrderFound = $collection->getColumnValues('increment_id');
        $this->orderWrong = array_values(array_diff($filterOrders, $listOrderFound));
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function orderFilter($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());
        if ($value == $this->_keyReset) {
            $this->setOrderParams();
            $column->getFilter()->setValue('');
            return $this;
        }
        $filterOrders = $this->getOrderParams();
        $orders = preg_split('/\s*,\s*/u', $value);
        foreach ($orders as $order) {
            if (!in_array($order, $filterOrders)) {
                $filterOrders[] = $order;
                // find & replace order have space character
                if (preg_match('/\s+/', $order)) {
                    $filterOrders[] = preg_replace('/\s+/', '', $order);
                }
            }
        }
        if (count($filterOrders) >= 1) {
            $listOrder = [];
            $listOrderRaw = [];
            $listPersonalizedId = [];
            foreach ($filterOrders as $item) {
                if ($this->personalizedIdFilterField) {
                    if (strlen($item) < 10) {
                        $listPersonalizedId[] = ['like' => '%' . $item . '%'];
                        continue;
                    }
                }
                $listOrderRaw[] = $item;
                $listOrder[] = ['like' => '%' . $item . '%'];
            }
            if ($listOrder) {
                // if (order has fulltext
                $col = clone $column;
                $filterIndex = explode('.', $this->orderFilterField);
                $col->setFilterIndex($this->orderFilterField);
                $col->setIndex(array_pop($filterIndex));
                $mainTable = $this->getTableField($collection, $col);
                $columns = $this->getFulltextIndexColumns($collection, $mainTable, $col);
                if ($columns) {
                    $columns = $this->addTableAliasToColumns($columns, $collection, $mainTable);
                    $fulltext = [];
                    foreach ($listOrderRaw as $order) {
                        $keyword = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $order);
                        $keyword = \Safe\preg_replace('/\s+/u', ' ', $keyword);
                        $fulltext[]  = '*' . preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $keyword) . '*';
                    }
                    if (!empty($fulltext)) {
                        $collection->getSelect()->where(
                            'MATCH(' . implode(',', $columns) . ') AGAINST(? IN BOOLEAN MODE)',
                            '(' . implode(')(', $fulltext) . ')'
                        );
                    }
                }
                $collection->addFieldToFilter($this->orderFilterField, $listOrder);
            }
            if ($this->personalizedIdFilterField && $listPersonalizedId) {
                $collection->addFieldToFilter($this->personalizedIdFilterField, $listPersonalizedId);
            }
        }
        $cloneCollection = clone $collection;
        if ($cloneCollection->getSize()) {
            $this->setOrderParams($filterOrders);
            $this->checkOrderWrong($cloneCollection, $filterOrders);
            $column->getFilter()->setValue(implode(",", $filterOrders));
        } else {
            $this->orderWrong = $filterOrders;
            $this->setOrderParams();
            $column->getFilter()->setValue('');
        }
        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function addMultipleFilter($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if (isset($value)) {
            if (!is_array($value)) {
                $value = explode(',', $value);
            }
            if (($key = array_search('null', $value)) !== false) {
                $value[$key] = "";
                $collection->addFieldToFilter([$field, $field], [['in' => $value], ['null' => true]]);
            } else
                $collection->addFieldToFilter($field, ['in' => $value]);
        }
        return $collection;
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function filterGoldType($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value) {
            $collection->addFieldToFilter($field, ['in' => $value]);
        }
        return $collection;
    }

    /**
     * // support massaction export
     * @param $value
     * @return $this
     */
    public function setIsExport($value)
    {
        $this->_isExport = $value;
        return $this;
    }

    /**
     * support massaction export
     * @param $value
     * @return $this
     */
    public function setIsMassactionExport($value)
    {
        $this->isMassActionExport = $value;
        return $this;
    }

    /**
     * support massaction export
     * @return Extended
     */
    public function massExportGetCollection()
    {
        return $this->_prepareCollection();
    }

    /**
     * @return $this
     */
    public function prepareGrid()
    {
        $this->_prepareGrid();
        $this->_prepareCollection();
        return $this;
    }

    /**
     * @param string $sheetName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getExcelFile($sheetName = '')
    {
        $this->_prepareExport();
        $this->_isExport = true;
        $this->_prepareGrid();
        $collection = $this->getCollection();
        $collection->getSelect()->limit();
        $collection->setPageSize(0);
        $collection->load();
        $convert = new \Magento\Framework\Convert\Excel(
            $collection->getIterator(),
            [$this, 'getRowRecord']
        );

        // phpcs:ignore Magento2.Security.InsecureFunction
        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.xml';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');
        $stream->lock();

        $convert->setDataHeader($this->_getExportHeaders());
        if ($this->getCountTotals()) {
            $convert->setDataFooter($this->_getExportTotals());
        }

        $convert->write($stream, $sheetName);
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        ];
    }

    /**
     * @return string
     */
    public function getExcel($fileName = '', $type = 'Xlsx', $nameSheet = 'Sheet1')
    {
        $this->_prepareExport();
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();
        $headers = [];
        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $headers[] = '<strong>' . $column->getHeader() . '</strong>';
            }
        }
        $data[] = $headers;

        foreach ($this->getCollection() as $item) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowFieldExport($item);
                }
            }
            $data[] = $row;
        }

        if ($this->getCountTotals()) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $row[] = $column->getRowField($this->getTotals());
                }
            }
            $data[] = $row;
        }
        if ($fileName) {
            $content = '<table>';
            foreach ($data as $values) {
                $content .= '<tr>';
                foreach ($values as $value) {
                    $content .= '<td>' . $value . '</td>';
                }
                $content .= '</tr>';
            }
            $content .= '</table>';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($content);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($nameSheet);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $type);
            $writer->save($fileName);
            $result = file_get_contents($fileName);
            unlink($fileName);
            return $result;
        }
        $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($data));
        return $convert->convert($nameSheet);
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function filterEqualCondition($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value) {
            $collection->addFieldToFilter($field, $value);
        }
        return $collection;
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function filterFullText($collection, $column)
    {
        $filter = $column->getFilter();
        if ($filter->getValue()) {
            $this->apply($collection, $filter, $column);
        }
        return $collection;
    }

    /**
     * Build fulltext query
     * @param $str string
     * @return false|string Fulltext search query string
     */
    protected function genFullTextShortParam($str)
    {
        $words = preg_split('/[+\-><\(\)~*\"@]|[^\p{L}\p{N}_]/u', $str);
        $len = count($words);
        $keyword = [];
        foreach ($words as $k => $word) {
            if (mb_strlen($word) >= static::FULLTEXT_MIN_WORD_LEN) {
                if ($k === 0) {
                    $keyword[] = '*' . $word;
                    continue;
                }
                if (($k + 1) === $len) {
                    $word = $word . '*';
                }
                $keyword[] = '+' . $word;
            }
        }
        if (count($keyword) > 0) {
            return implode(' ', $keyword);
        }
        return false;
    }

    /**
     * Combine Fulltext search (boolean mode), like query
     * Apply fulltext search (boolean mode) when any word on search has length larger than FULLTEXT_MIN_WORD_LEN
     * @param $collection \OpenTechiz\PersonalizedProduct\Model\ResourceModel\Item\Collection
     * @param $column \Magento\Backend\Block\Widget\Grid\Column
     */
    public function filterFullTextShort($collection, $column)
    {
        $filter = $column->getFilter();
        $val = $filter->getValue();
        if (empty($val)) {
            return $collection;
        }

        $search = $this->genFullTextShortParam($val);

        $mainTable = $this->getTableField($collection, $column);
        $columns = $this->getFulltextIndexColumns($collection, $mainTable, $column);
        if (!empty($columns) && $search) {
            $columns = $this->addTableAliasToColumns($columns, $collection, $mainTable);
            $collection->getSelect()->where(
                'MATCH(' . implode(',', $columns) . ') AGAINST(? IN BOOLEAN MODE)',
                $search
            );
        }
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        if ($field && isset($condition)) {
            $collection->addFieldToFilter($field, $condition);
        }
        return $collection;
    }

    /**
     * @param array      $columns
     * @param AbstractDb $collection
     * @param            $indexTable
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    protected function addTableAliasToColumns(array $columns, AbstractDb $collection, $indexTable)
    {
        $alias = '';
        foreach ($collection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($indexTable == $data['tableName']) {
                $alias = $tableAlias;
                break;
            }
        }
        if ($alias) {
            $columns = array_map(
                function ($column) use ($alias) {
                    return '`' . $alias . '`.' . $column;
                },
                $columns
            );
        }

        return $columns;
    }

    /**
     * @param Collection $collection
     * @param            $filter
     * @throws \Zend_Db_Select_Exception
     */
    public function apply(Collection $collection, $filter, $column)
    {
        $mainTable = $this->getTableField($collection, $column);
        $columns = $this->getFulltextIndexColumns($collection, $mainTable, $column);
        if (!$columns) {
            return;
        }

        $columns = $this->addTableAliasToColumns($columns, $collection, $mainTable);
        $collection->getSelect()
            ->where(
                'MATCH(' . implode(',', $columns) . ') AGAINST(? IN BOOLEAN MODE)',
                str_replace(' ', ' +', trim($filter->getValue()))
            );
    }

    /**
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function getTableField($collection, $column)
    {
        $tableName = $collection->getMainTable();
        if ($filterIndex = $column->getFilterIndex()) {
            $filterIndex = explode('.', $filterIndex);
            $aliasTable = $filterIndex[0];
            foreach ($collection->getSelect()->getPart('from') as $tableAlias => $data) {
                if ($tableAlias == $aliasTable) {
                    $tableName = $data['tableName'];
                    break;
                }
            }
        }
        return $tableName;
    }

    /**
     * @param AbstractDb $collection
     * @param            $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns(AbstractDb $collection, $indexTable, $column)
    {
        $columnName = $column->getIndex();
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                if (in_array($columnName, $index['COLUMNS_LIST'])) {
                    return $index['COLUMNS_LIST'];
                }
            }
        }
        return [];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHeaderAdditionalHtml()
    {
        $html = '';
        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlBulkFilterBarcode()
    {
        $html = '';
        if ($this->barcodeFilterField && $this->_bulkbarcodescan) {
            $html .= $this->getLayout()->createBlock('Magentiz\ConfigFinder\Block\Widget\Grid\BulkBarcodeScan')
                ->setData(
                    [
                        "object_grid_name" => $this->getJsObjectName(),
                        "grid_column_filter" => $this->getId() . "_filter_" . $this->getColumnFilter(),
                        "grid_table" => $this->getId() . "_table",
                        "object_grid_massaction_name" => $this->getMassactionBlock()->getJsObjectName(),
                        "key_reset" => $this->_keyReset,
                        "barcode_list_wrong" => $this->barcodeWrong,
                        "current_barcode_scan" => $this->checkHaveFilter('scan_barcode') ? $this->currentFilterBarcode:[],
                        "show_message" => $this->_request->getParam('isAjax', 0) ? 1 : 0,
                    ]
                )->toHtml();
        }
        return $html;
    }



    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlBulkFilterOrder()
    {
        $html = '';
        return $html;
    }

    /**
     * @return string
     */
    protected function getColumnFilter()
    {
        return 'scan_barcode';
    }

    /**
     * @return $this
     */
    protected function _prepareExport() {
        return $this;
    }

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCsv()
    {
        $this->_prepareExport();
        return parent::getCsv();
    }

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getCsvFile()
    {
        $this->_prepareExport();
        return parent::getCsvFile();
    }

    /**
     * @return string
     */
    public function getExtendInfoRecordFound()
    {
        $html = '';
        return $html;
    }

    /**
     * @return array
     */
    public function getNoteGrid()
    {
        return [];
    }
}
