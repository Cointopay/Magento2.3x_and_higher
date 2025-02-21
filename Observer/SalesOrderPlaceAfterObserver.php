<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var $scopeConfig;
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var $merchantId
     **/
    protected $merchantId;

    /**
     * @var $merchantKey
     **/
    protected $merchantKey;

    /**
     * @var $coinId
     **/
    protected $coinId;

    /**
     * @var $type
     **/
    protected $type;

    /**
     * @var $orderTotal
     **/
    protected $orderTotal;

    /**
     * @var $_curlUrl
     **/
    protected $_curlUrl;

    /**
     * @var currencyCode
     **/
    protected $currencyCode;

    /**
     * @var $_storeManager
     **/
    protected $_storeManager;

    /**
     * @var $securityKey
     **/
    protected $securityKey;

    /**
     * Merchant ID
     */
    protected const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';

    /**
     * Merchant COINTOPAY API Key
     */
    protected const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_key';

    /**
     * Merchant COINTOPAY SECURITY Key
     */
    protected const XML_PATH_MERCHANT_SECURITY = 'payment/cointopay_gateway/merchant_gateway_security';

    /**
     * Merchant COINTOPAY ORDER STATUS
     */
    protected const XML_PATH_ORDER_STATUS = 'payment/cointopay_gateway/order_status';

    /**
     * API URL
     **/
    protected const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * @var $response
     **/
    protected $response = [] ;

    /**
     * @var \Magento\Framework\App\RequestInterface
     **/
    protected $_request;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     **/
    protected $_historyFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     **/
    protected $_orderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     **/
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     **/
    protected $_cookieManager;

    /**
     * @var $paidStatus
     **/
    protected $orderStatus;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_response;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_urlRewrite;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_urlRewriteFactory;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $urlFinder;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     * @param \Magento\Framework\Json\DecoderInterface $decoder
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\ResponseFactory $_response
     * @param \Magento\UrlRewrite\Model\UrlRewrite $_urlRewrite
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $_urlRewriteFactory
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ResponseFactory $_response,
        \Magento\UrlRewrite\Model\UrlRewrite $_urlRewrite,
        \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $_urlRewriteFactory,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
    ) {
        $this->logger          = $logger;
        $this->_cookieManager  = $cookieManager;
        $this->_jsonEncoder    = $encoder;
        $this->_jsonDecoder    = $decoder;
        $this->_curl           = $curl;
        $this->scopeConfig     = $scopeConfig;
        $this->_storeManager   = $storeManager;
        $this->pageFactory    = $pageFactory;
        $this->_request        = $request;
        $this->_historyFactory = $historyFactory;
        $this->_coreSession    = $coreSession;
        $this->_orderFactory   = $orderFactory;
        $this->_response       = $_response;
        $this->_urlRewrite     = $_urlRewrite;
        $this->_urlRewriteFactory = $_urlRewriteFactory;
        $this->urlFinder          = $urlFinder;
    }//end __construct()

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /*
         *
         *
         * @var $orderInstance Order
         */
        $order           = $observer->getData('order');
        $additional_data = "";
        // $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        $this->_coreSession->start();
        // $this->coinId =  $this->_coreSession->getCoinId(); //$_SESSION['coin_id'];
        // $this->coinId = $_SESSION['coin_id'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $orderObject = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $lastOrderId         = $order->getIncrementId();
        $this->orderTotal    = $order->getGrandTotal();
        $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
        $storeManager        = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store      = $storeManager->getStore();
        $baseUrl    = $store->getBaseUrl();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        // // getting data from file
        // $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        // $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        if ($payment_method_code == 'cointopay_gateway') {
            $additional_data = $order->getPayment()->getAdditionalInformation();
            // throw new \Magento\Framework\Exception\LocalizedException(__(var_dump($additional_data)));
            $this->coinId  = $additional_data['transaction_result'];
            $response      = $this->sendCoins($lastOrderId);
            $orderresponse = $this->_jsonDecoder->decode($response);
            if (!isset($orderresponse['TransactionID'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__($response));
            }

            // $_SESSION['cointopay_response'] = $response;
            $this->_coreSession->setCointopayresponse($response);
            $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
            $customerSession->setCoinresponse($response);
            // set value in customer session
            $order->setExtOrderId($orderresponse['TransactionID']);
            $this->orderStatus = $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS, $storeScope);
            $order->setState($this->orderStatus)->setStatus($this->orderStatus);
            $order->save();
            $UrlRC = $this->_urlRewrite->getCollection()->addFieldToFilter('request_path', 'checkout/onepage/success');
            $deleteItem           = $UrlRC->getFirstItem();
            if ($UrlRC->getFirstItem()->getId()) {
                // target path does exist
                $filterData      = [UrlRewrite::REQUEST_PATH => 'checkout/onepage/success'];
                $rewrite         = $this->urlFinder->findOneByData($filterData);
                $urlRewriteModel = $this->_urlRewriteFactory->create();
                $deleteItem->delete();
                $customerSession->setCoinStoreId($rewrite->getStoreId());
                $customerSession->setCoinTargetPath($rewrite->getTargetPath());
            }
        }//end if
    }//end execute()

    /**
     * Cointopay request.
     *
     * @param int $orderId
     * @return json response
     **/
    private function sendCoins($orderId = 0)
    {
        $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager     = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store            = $storeManager->getStore();
        $baseUrl          = $store->getBaseUrl();
        $storeScope       = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->merchantKey  = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
        $this->securityKey  = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope);
        $this->currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $ctp_api_url = 'https://cointopay.com/MerchantAPI?Checkout=true';
        $ctp_api_req = '&MerchantID='.$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId;
        $ctp_api_req_a = '&CustomerReferenceNr='.$orderId.'&SecurityCode='.$this->securityKey.'&output=json';
        $ctp_api_req_b = '&inputCurrency='.$this->currencyCode.'&transactionconfirmurl='.$baseUrl;
        $ctp_api_req_c = 'paymentcointopay/order/&transactionfailurl='.$baseUrl.'paymentcointopay/order/';
        $ctp_api_req_d = '&transactionfailurl='.$baseUrl.'paymentcointopay/order/';
        $this->_curlUrl = $ctp_api_url.$ctp_api_req.$ctp_api_req_a.$ctp_api_req_b.$ctp_api_req_c.$ctp_api_req_d;
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        return $response;
    }//end sendCoins()
}//end class
