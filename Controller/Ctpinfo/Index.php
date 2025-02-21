<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Controller\Ctpinfo;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{

    /**
     * PageFactory variable
     *
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context Application context object.
     * @param PageFactory $pageFactory Generates page instances.
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }//end __construct()

    /**
     * Execute the action
     *
     * @return void
     */
    public function execute()
    {
        $objectManager     = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession   = $objectManager->get(\Magento\Customer\Model\Session::class);
        $resultRedirect    = $objectManager->get(\Magento\Framework\Controller\Result\RedirectFactory::class);
        $resultRedirectObj = $resultRedirect->create();
        if ($customerSession->isLoggedIn() === false) {
            $resultRedirectObj->setPath('customer/account/login');
            return $resultRedirectObj;
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Cointopay Crypto Invoice Detail'));
        $this->_view->renderLayout();
    }//end execute()
}//end class
