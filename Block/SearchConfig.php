<?php
/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\ConfigFinder\Block;

class SearchConfig extends \Magento\Backend\Block\Template
{
    /**
     * @return array
     */
    public function getWidgetInitOptions()
    {
        return [
            'searchConfig' => [
                'source' => $this->getUrl('config_finder/result/index')
            ]
        ];
    }
}
