<?php
namespace Magentiz\ConfigFinder\Block\Widget\Grid;

use Magento\Backend\Block\Template;

class BulkOrderScan extends Template
{
    public function _construct(){
        parent::_construct();
        $this->setTemplate('Magentiz_ConfigFinder::widget/grid/bulk_order_scan.phtml');
    }
}
