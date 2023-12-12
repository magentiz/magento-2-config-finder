<?php

namespace Magentiz\ConfigFinder\Plugin\App;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Http;
use Magento\Framework\Debug;

class HttpPlugin
{
    protected $state;

    protected $logger;

    protected $encryptor;

    protected $backendUrl;

    protected $messageManager;

    protected $redirect;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->state = $state;
        $this->backendUrl = $backendUrl;
        $this->encryptor = $encryptor;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->logger = $logger;
    }

    public function aroundCatchException(
        Http $subject,
        callable $proceed,
        Bootstrap $bootstrap,
        \Exception $exception
    ) {
        if ($this->state->getAreaCode() == 'adminhtml' && $this->state->getMode() == 'production') {
            $this->handleExceptionError($bootstrap, $exception);
            return true;
        }
        return $proceed($bootstrap, $exception);
    }

    private function handleExceptionError(Bootstrap $bootstrap, \Exception $exception)
    {
        $reportData = [
            $exception->getMessage(),
            Debug::trace(
                $exception->getTrace(),
                true,
                false,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            )
        ];
        $reportData['report_id'] = $this->encryptor->getHash(implode('', $reportData));
        $this->logger->critical($exception, ['report_id' => $reportData['report_id']]);
        $redirectUrl = $this->redirect->getRedirectUrl();
        if (strpos($redirectUrl, $this->backendUrl->getAreaFrontName()) === false) {
            $dashboardRoute = $this->backendUrl->getStartupPageUrl();
            $redirectUrl = $this->backendUrl->getUrl($dashboardRoute);
        }
        $this->messageManager->addErrorMessage(__($reportData[0]));
        header('Location: ' . $redirectUrl);
        exit();
    }
}