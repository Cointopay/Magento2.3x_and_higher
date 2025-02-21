<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Controller\Index;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\App\ResponseInterface;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Registry;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Context variable
     *
     * @var Context
     */
    protected $context;

    /**
     * PageFactory variable
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * EncoderInterface variable
     *
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * SessionManagerInterface variable
     *
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * JsonFactory variable
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * ObjectManagerInterface variable
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Session variable
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * OrderFactory variable
     *
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * InvoiceSender variable
     *
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * ScopeConfigInterface variable
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Curl variable
     *
     * @var Curl
     */
    protected $curl;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $merchantKey;

    /**
     * @var string
     */
    protected $coinId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $orderTotal;

    /**
     * @var string
     */
    protected $curlUrl;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * StoreManagerInterface variable
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $securityKey;

    /**
     * @var string
     */
    protected $paidStatus;

    /**
     * Merchant ID
     */
    public const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';

    /**
     * Merchant COINTOPAY API Key
     */
    public const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_key';

    /**
     * Merchant COINTOPAY SECURITY Key
     */
    public const XML_PATH_MERCHANT_SECURITY = 'payment/cointopay_gateway/merchant_gateway_security';

    /**
     * Merchant COINTOPAY SECURITY Key
     */
    public const XML_PATH_PAID_ORDER_STATUS = 'payment/cointopay_gateway/order_status_paid';

    /**
     * API URL
     */
    public const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * Response array
     *
     * @var array
     */
    protected $response = [];

    /**
     * Registry variable
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Processes the payment request.
     *
     * @param Context                 $context           Application context object.
     * @param EncoderInterface        $encoder           Handles encoding functionalities.
     * @param Curl                    $curl              Handles HTTP requests.
     * @param ScopeConfigInterface    $scopeConfig       Retrieves store configurations.
     * @param StoreManagerInterface   $storeManager      Manages store-related data.
     * @param PageFactory             $pageFactory       Generates page instances.
     * @param JsonFactory             $resultJsonFactory Creates JSON responses.
     * @param SessionManagerInterface $coreSession       Handles core session management.
     * @param ObjectManagerInterface  $objectmanager     Provides object dependencies.
     * @param Session                 $checkoutSession   Manages checkout session data.
     * @param OrderFactory            $orderFactory      Creates order instances.
     * @param Registry                $registry          Manages Magento registry data.
     * @param InvoiceSender           $invoiceSender     Handles invoice sending process.
     */
    public function __construct(
        Context $context,
        EncoderInterface $encoder,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PageFactory $pageFactory,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $coreSession,
        ObjectManagerInterface $objectmanager,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Registry $registry,
        InvoiceSender $invoiceSender
    ) {
        $this->context      = $context;
        $this->jsonEncoder  = $encoder;
        $this->curl         = $curl;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->pageFactory  = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreSession       = $coreSession;
        $this->objectManager     = $objectmanager;
        $this->checkoutSession   = $checkoutSession;
        $this->orderFactory      = $orderFactory;
        $this->registry          = $registry;
        $this->invoiceSender     = $invoiceSender;
        parent::__construct($context);
    }//end __construct()

    /**
     * Execute the action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest() === true) {
            $storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->coinId      = $this->getRequest()->getParam('paymentaction');
            $this->merchantId  = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
            $this->securityKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope);
            $this->paidStatus  = $this->scopeConfig->getValue(self::XML_PATH_PAID_ORDER_STATUS, $storeScope);
            $type = $this->getRequest()->getParam('type');
            $this->currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
            if ($type === 'status') {
                $response = $this->getStatus($this->coinId);
                if ($response === 'paid') {
                    $orderId = $this->getRealOrderId();
                    $order   = $this->objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
                    if ($order->canInvoice() === true) {
                        $invoice = $order->prepareInvoice();
                        $invoice->getOrder()->setIsInProcess(true);
                        $invoice->register()->pay();
                        $invoice->save();
                    }

                    $order->setState($this->paidStatus)->setStatus($this->paidStatus);
                    $order->save();
                    if (isset($invoice) === true) {
                        $this->invoiceSender->send($invoice);
                    }
                }

                $result = $this->resultJsonFactory->create();
                return $result->setData(['status' => $response]);
            } else {
                $this->coreSession->start();
                $this->coreSession->setCoinid($this->coinId);
                $isVerified = $this->verifyOrder();

                $result = $this->resultJsonFactory->create();
                if ($isVerified === 'success') {
                    return $result->setData(['status' => 'success', 'coindid' => $this->coinId]);
                } else {
                    return $result->setData(['status' => 'error', 'message' => $isVerified]);
                }
            }//end if
        }//end if
    }//end execute()

    /**
     * Pay Order
     *
     * @return json response
     **/
    private function payOrder()
    {
        $this->orderTotal = $this->getCartAmount();
        $this->curlUrl    = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID='
        .$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId
        .'&CustomerReferenceNr=buy%20something%20from%20me&SecurityCode='
        .$this->securityKey.'&output=json&inputCurrency='.$this->currencyCode;
        $this->curl->get($this->curlUrl);
        $response = $this->curl->getBody();
        $result = $this->resultJsonFactory->create();
        return $result->setData($response);
    }//end payOrder()

    /**
     * Get Cart Amount
     *
     * @return Total order amount from cart
     **/
    private function getCartAmount()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart          = $objectManager->get(\Magento\Checkout\Model\Cart::class);
        return $cart->getQuote()->getGrandTotal();
    }//end getCartAmount()

    /**
     * Get Status
     *
     * @param int $transactionId The ID of the transaction.
     *
     * @return string payment status
     **/
    private function getStatus($transactionId)
    {
        $storeScope       = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->curlUrl    = 'https://cointopay.com/CloneMasterTransaction?MerchantID='
        .$this->merchantId.'&TransactionID='.$transactionId.'&output=json';
        $this->curl->get($this->curlUrl);
        $response = $this->curl->getBody();
        $decoded  = json_decode($response);
        return $decoded[1];
    }//end getStatus()

    /**
     * Verify Order
     *
     * @return response
     **/
    private function verifyOrder()
    {
        $this->orderTotal = $this->getCartAmount();
        $this->curlUrl    = 'https://cointopay.com/MerchantAPI?Checkout=true&MerchantID='
        .$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId
        .'&CustomerReferenceNr=buy%20something%20from%20me&SecurityCode='
        .$this->securityKey.'&output=json&inputCurrency='.$this->currencyCode.'&testcheckout';
        $this->curl->get($this->curlUrl);
        $response = $this->curl->getBody();
        if ($response === '"testcheckout success"') {
            return 'success';
        }

        return $response;
    }//end verifyOrder()

    /**
     * Get Order ID
     *
     * @return orderid
     **/
    public function getRealOrderId()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        return $lastOrderId;
    }//end getRealOrderId()
}//end class
