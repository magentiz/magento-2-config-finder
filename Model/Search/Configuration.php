<?php

namespace Magentiz\ConfigFinder\Model\Search;

use Magento\Framework\DataObject;

class Configuration extends DataObject
{
    protected $_configStructure;
    protected $_configStructureData;

    protected $_url;

    public function __construct(
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Structure\Data $configStructureData,
        \Magento\Framework\UrlInterface $url,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_configStructure = $configStructure;
        $this->_configStructureData = $configStructureData;
        $this->_url = $url;
    }

    public function load()
    {
        $results = [];

        if (! $this->getQuery()) {
            $this->setResults($results);
            return $this;
        }

        $tabs = $this->_configStructure->getTabs();

        $results = $this->prepareDataForSearch($tabs);

        foreach ($results as $index => $item) {
            if (strpos(strtolower($item['name']), strtolower($this->getQuery())) === false) {
                unset($results[$index]);
            }
        }

        $this->setResults($results);

        return $this;
    }

    public function setQuery($query)
    {
        return $this->setData('query', $query);
    }

    public function getQuery()
    {
        return $this->getData('query');
    }

    public function setResults($results)
    {
        return $this->setData('results', $results);
    }

    public function getResults()
    {
        return $this->getData('results');
    }

    protected function prepareDataForSearch($tabs)
    {
        $data = [];

        foreach ($tabs as $tab) {
            $tabLabel = $tab->getLabel();
            foreach ($tab->getChildren() as $section) {
                $sectionId = $section->getId();
                $sectionLabel = $section->getLabel();
                $data[] = [
                    "id" => $sectionId,
                    "tabId" => $sectionId,
                    "name" => $sectionLabel,
                    "description" => "Configuration -> $tabLabel -> $sectionLabel",
                    "url" => $this->_url->getUrl("admin/system_config/edit/section/$sectionId")
                ];

                foreach ($section->getChildren() as $group) {
                    $groupId = $group->getId();
                    $groupLabel = $group->getLabel();
                    $data[] = [
                        "id" => $groupId,
                        "tabId" => $sectionId,
                        "name" => $groupLabel,
                        "description" => "Configuration -> $tabLabel -> $sectionLabel -> $groupLabel",
                        "url" => $this->_url->getUrl("admin/system_config/edit/section/$sectionId") . "#$sectionId" . "_" . "$groupId" . "-link"
                    ];
                    foreach ($group->getChildren() as $field) {
                        $fieldId = $field->getId();
                        $fieldLabel = $field->getLabel();
                        $type = $field->getAttribute('_elementType');
                        $data[] = [
                            "id" => $fieldId,
                            "tabId" => $sectionId,
                            "name" => $fieldLabel,
                            'type' => $type,
                            "description" => "Configuration -> $tabLabel -> $sectionLabel -> $groupLabel -> $fieldLabel",
                            "url" => $this->_url->getUrl("admin/system_config/edit/section/$sectionId") . "#$sectionId" . "_" . "$groupId" . ($type == 'field' ? "-link" : '')
                        ];
                    }
                }
            }
        }
        return $data;
    }
}