<?php

namespace Magentiz\ConfigFinder\Plugin\Backend\Block\Widget\Grid\Column;

class Store {
    public function afterGetHtml(\Magento\Backend\Block\Widget\Grid\Column\Filter\Store $object, $result){
        if(str_contains($result, '[ deleted ]')){
            $result = str_replace('[ deleted ]', '', $result);
        }

        return $result;
    }

}
