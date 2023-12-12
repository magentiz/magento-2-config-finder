<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

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