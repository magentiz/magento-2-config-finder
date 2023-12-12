<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\ConfigFinder\Block\Store;

class Switcher extends \Magento\Backend\Block\Store\Switcher
{
    protected function _toHtml()
    {
        $this->setTemplate('Magentiz_ConfigFinder::store/switcher.phtml');
        return parent::_toHtml();
    }
}
