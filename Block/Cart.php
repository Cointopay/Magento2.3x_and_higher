<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Block;

class Cart extends \Magento\Sales\Block\Order\Totals
{
	protected $_context;
    protected $customerSession;
    protected $_resultFactory;
	protected $_cart;
	protected $_redirect;
	
    
    public function __construct(
	    \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Checkout\Model\Cart $_cart,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->customerSession = $customerSession;
        $this->_resultFactory = $resultFactory;
		$this->_cart = $_cart;
		$this->_redirect = $redirect;
    }

    public function getOrder()
    {
		$quote=$this->_cart->getQuote();
		$totalItems=count($quote->getAllItems());
		if($totalItems==0){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
		$orderId   =   $orderDatamodel->getId();
		$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        return  $order;
		}
		return false;
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }
	
	public function getPrevpage()
    {
		$resultRedirect = $this->_resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setRefererUrl();
		return $this->_redirect->getRedirectUrl();
    }

}