<?php
namespace Cointopay\PaymentGateway\Controller\Ctpinfo;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
class Index extends Action {
    private $pageFactory;
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $resultRedirect = $objectManager->get('Magento\Framework\Controller\Result\RedirectFactory');
        $resultRedirect_obj = $resultRedirect->create();
        if (!$customerSession->isLoggedIn()){
          $resultRedirect_obj->setPath('customer/account/login');
          return $resultRedirect_obj;
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Cointopay Crypto Invoice Detail'));
        $this->_view->renderLayout();
    }
}
