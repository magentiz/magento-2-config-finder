<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\ConfigFinder\Model\Search;

use Magento\Framework\DataObject;

class Configuration extends DataObject
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;
    /**
     * @var \Magento\Config\Model\Config\Structure\Data
     */
    protected $_configStructureData;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Configuration constructor.
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Model\Config\Structure\Data $configStructureData
     * @param \Magento\Framework\UrlInterface $url
     * @param array $data
     */
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

    /**
     * @return $this
     */
    public function load()
    {
        $results = [];

        if (!$this->getQuery()) {
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

    /**
     * @param $query
     * @return mixed
     */
    public function setQuery($query)
    {
        return $this->setData('query', $query);
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->getData('query');
    }

    /**
     * @param $results
     * @return mixed
     */
    public function setResults($results)
    {
        return $this->setData('results', $results);
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->getData('results');
    }

    /**
     * @param $tabs
     * @return array
     */
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
                    "description" => "Configuration => $tabLabel => $sectionLabel",
                    "url" => $this->_url->getUrl("admin/system_config/edit/section/$sectionId")
                ];

                foreach ($section->getChildren() as $group) {
                    $groupId = $group->getId();
                    $groupLabel = $group->getLabel();
                    $data[] = [
                        "id" => $groupId,
                        "tabId" => $sectionId,
                        "name" => $groupLabel,
                        "description" => "Configuration => $tabLabel => $sectionLabel => $groupLabel",
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
                            "description" => "Configuration => $tabLabel => $sectionLabel => $groupLabel => $fieldLabel",
                            "url" => $this->_url->getUrl("admin/system_config/edit/section/$sectionId") . "#$sectionId" . "_" . "$groupId" . ($type == 'field' ? "-link" : '')
                        ];
                    }
                }
            }
        }
        return $data;
    }
}