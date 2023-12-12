<?php

namespace Magentiz\ConfigFinder\Plugin\Backend\Block;

class Menu
{
    public function afterToHtml(\Magento\Backend\Block\Menu $subject, $html)
    {
        $js = $subject->getLayout()->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Magentiz_ConfigFinder::js.phtml')
            ->toHtml();

        return $html . $js;
    }
}
