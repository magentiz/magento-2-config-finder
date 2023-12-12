<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\ConfigFinder\Controller\Adminhtml\Result;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magentiz\ConfigFinder\Model\Search\Configuration as SearchConfiguration;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var SearchConfiguration
     */
    protected $searchConfiguration;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SearchConfiguration $searchConfiguration
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SearchConfiguration $searchConfiguration
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->searchConfiguration = $searchConfiguration;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->handleAjax();
    }

    /**
     * @return mixed
     */
    protected function handleAjax()
    {
        $resultJson = $this->resultJsonFactory->create();
        $items = [];

        if (!$this->_authorization->isAllowed('Magento_Config::config')) {
            $items['error'] = [
                'id' => 'error',
                'type' => __('Error'),
                'name' => __('Access Denied.'),
                'description' => __('You need more permissions to do this.'),
            ];

            return $resultJson->setData($items);
        }

        $query = $this->getRequest()->getParam('query');
        $results = $this->searchConfiguration->setQuery($query)->load()->getResults();
        $items = array_merge_recursive($items, $results);

        return $resultJson->setData($items);
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Config::config');
    }
}