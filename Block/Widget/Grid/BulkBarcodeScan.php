<?php
namespace Magentiz\ConfigFinder\Block\Widget\Grid;

use Magento\Backend\Block\Template;

class BulkBarcodeScan extends Template
{
    public function _construct(){
        parent::_construct();
        $this->setTemplate('Magentiz_ConfigFinder::widget/grid/bulk_barcode_scan.phtml');
    }
}
