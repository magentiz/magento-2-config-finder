<?php

namespace Magentiz\ConfigFinder\Model\Menu\Item;

class Validator extends \Magento\Backend\Model\Menu\Item\Validator
{
    public function __construct()
    {
        parent::__construct();
        $this->_validators['toolTip'] = new \Zend_Validate_StringLength(['min' => 3]);
    }
}
