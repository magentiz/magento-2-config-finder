<?php

namespace Magentiz\ConfigFinder\Block\Widget\Grid\Massaction;

class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    /**
     * Get grid ids in JSON format
     *
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }
        $idList = $this->getParentBlock()->getGridIds();
        if ($idList !== null) {
            return implode(',', $idList);
        }
        return parent::getGridIdsJson();
    }
}
