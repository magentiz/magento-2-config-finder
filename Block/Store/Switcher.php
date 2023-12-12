<?php

namespace Magentiz\ConfigFinder\Block\Store;

class Switcher extends \Magento\Backend\Block\Store\Switcher
{
    protected function _toHtml()
    {
        $this->setTemplate('Magentiz_ConfigFinder::store/switcher.phtml');
        return parent::_toHtml();
    }
}
