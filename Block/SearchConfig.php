<?php

namespace Magentiz\ConfigFinder\Block;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class SearchConfig extends \Magento\Backend\Block\Template
{

    public function getWidgetInitOptions()
    {
        return [
            'searchConfig' => [
                'source' => $this->getUrl('config_finder/result/index')
            ]
        ];
    }
}