<?php
namespace Magentiz\ConfigFinder\Block\Widget;
/**
 * 
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
	/**
     * @var string
     */
    protected $_template = 'Magentiz_ConfigFinder::widget/grid.phtml';

   /**
    * get additional javascrip
    *
    * @return tring
    */
    public function getAdditionalJavaScript() {
        $additionalJs = "";

        $additionalJs .= $this->getJsObjectName() . '.setValueToFilter = function(columnId, value) {
            var filterFieldId = "'. $this->getId() .'" + "_filter_" + columnId;
            if($(filterFieldId)) {
                $(filterFieldId).setValue(value);
            }
        };';
        /**@var filterData {columid: valueFilter}  */
        $additionalJs .= $this->getJsObjectName() . '.doFilterValues = function(filterData) {
            for(id in filterData) {
                if(filterData.hasOwnProperty(id)) {
                    this.setValueToFilter(id, filterData[id]);
                }
            }
            this.doFilter();
        };';

        if($this->getMassactionBlock() && $this->getMassactionBlock()->isAvailable()) {
            $additionalJs .= $this->getMassactionBlock()->getJsObjectName(). '.applyValue = function(valueId) {
                var selectId = "'.$this->getMassactionBlock()->getHtmlId().'" + "-select";
                if($(selectId)){
                    $(selectId).setValue(valueId);
                }
                '.$this->getMassactionBlock()->getJsObjectName().'.apply();
            };';
        }
        return $additionalJs;
    }
}