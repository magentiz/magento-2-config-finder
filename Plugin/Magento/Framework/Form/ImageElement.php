<?php

namespace Magentiz\ConfigFinder\Plugin\Magento\Framework\Form;

class ImageElement
{
    public function afterGetHtmlAttributes(
        \Magento\Framework\Data\Form\Element\Image $subject,
        array $result
    ) {
        $result[] = 'multiple';
        return $result;
    }
}
