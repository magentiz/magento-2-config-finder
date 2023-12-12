<?php

namespace Magentiz\ConfigFinder\Plugin\Backend\Block;

use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\Escaper;

class AnchorRenderer
{
    /**
     * @var MenuItemChecker
     */
    private $menuItemChecker;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param MenuItemChecker $menuItemChecker
     * @param Escaper $escaper
     */
    public function __construct(
        MenuItemChecker $menuItemChecker,
        Escaper $escaper
    ) {
        $this->menuItemChecker = $menuItemChecker;
        $this->escaper = $escaper;
    }

    public function afterRenderAnchor(
        \Magento\Backend\Block\AnchorRenderer $object,
        $result,
        $activeItem,
        Item $menuItem,
        $level
    ) {
        if ($level == 1 && $menuItem->getUrl() == '#') {
            return $result;
        }

        $result = str_replace($this->_renderItemAnchorTitle($menuItem),'', $result);

        if ($menuItem->hasTooltip() && $level) {
            $result = str_replace('</a>', "{$this->_getItemTooltipButton($menuItem)}</a>", $result);
        }

        return $result;
    }

    /**
     * @param Item $menuItem
     * @return string
     */
    private function _renderItemAnchorTitle($menuItem)
    {
        return $menuItem->hasTooltip() ? 'title="' . __($menuItem->getTooltip()) . '"' : '';
    }

    /**
     * @param Item $menuItem
     * @return string
     */
    private function _getItemTooltipButton($menuItem)
    {
        $str = "<span class='admin__field-tooltip tooltip-menu' title='".__($menuItem->getTooltip())."'>";
        $str .= "<span class='admin__field-tooltip-action action-help'></span>";
        $str .="</span>";
        return $str;
    }
}
