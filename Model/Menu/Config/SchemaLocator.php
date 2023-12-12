<?php

namespace Magentiz\ConfigFinder\Model\Menu\Config;

class SchemaLocator extends \Magento\Backend\Model\Menu\Config\SchemaLocator
{
    public function getSchema()
    {
        return realpath(__DIR__ . '/../../../etc/menu.xsd');
    }
}
