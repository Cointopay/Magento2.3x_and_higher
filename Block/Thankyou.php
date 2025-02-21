<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Block;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Thankyou extends Template
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var $merchantKey
     **/
    protected $merchantKey;

    /**
     * @var $merchantId
     **/
    protected $merchantId;

    /**
     * @var $_curlUrl
     **/
    protected $_curlUrl;

    /**
     * @var $transactionId
     **/
    protected $transactionId;

    /**
     * Merchant COINTOPAY API Key
     */
    protected const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_api_key';

    /**
     * Merchant ID
     */
    protected const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';
    
    /**
     * @var $response
     **/
    protected $response = [] ;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param BlockRepositoryInterface $blockRepository
     * @param Context $context
     * @param array $data = []
     **/
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        BlockRepositoryInterface $blockRepository,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, (array) $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderFactory   = $orderFactory;
        $this->scopeConfig     = $scopeConfig;
        $this->storeManager   = $storeManager;
        $this->coreSession    = $coreSession;
        $this->curl           = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
    }//end __construct()

    /**
     * Get Order
     *
     * @return array
     **/
    public function getOrder()
    {
        $order = $this->orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId()
        );
        if (empty($order)) {
            $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
            $orderDatamodel = $objectManager->get(\Magento\Sales\Model\Order::class)->getCollection()->getLastItem();
            $orderId        = $orderDatamodel->getId();
            $order   = $objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        }

        return  $order;
    }//end getOrder()

    /**
     * Get Customer Id
     *
     * @return int
     **/
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }//end getCustomerId()

    /**
     * Get Response
     *
     * @return string
     **/
    public function getCointopayHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession    = $objectManager->get(\Magento\Customer\Model\Session::class);
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
            return json_decode($cointopay_response);
        }

        return false;
    }//end getCointopayHtml()

    /**
     * Returns value view
     *
     * @return string | URL
     */
    public function getCoinsPaymentUrl()
    {
        return $this->getUrl("paymentcointopay");
    }//end getCoinsPaymentUrl()

    /**
     * Get Transactions
     *
     * @return bool
     */
    public function getTransactions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession    = $objectManager->get(\Magento\Customer\Model\Session::class);
        $cointopay_response = $customerSession->getCoinresponse();
        if (isset($cointopay_response)) {
            $customerSession->unsCoinresponse();
            return json_decode($cointopay_response);
        }

        return false;
    }//end getTransactions()
    
    /**
     * @inheritdoc
     */
    public function getobjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}//end class
