<?php
namespace Cointopay\PaymentGateway\Model;

class CointopayOrdersManagement
{

    /**
     * Context variable
     *
     * @var Context
     */
    protected $_context;

    /**
     * PageFactory variable
     *
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * EncoderInterface variable
     *
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * SessionManagerInterface variable
     *
     * @var SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * JsonFactory variable
     *
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * ObjectManagerInterface variable
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Session variable
     *
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * OrderFactory variable
     *
     * @var OrderFactory
     */
    protected $_orderFactory;

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
     * @var $paidStatus
     **/
    protected $paidStatus;

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
     * Merchant COINTOPAY SECURITY Key
     */
    protected const XML_PATH_PAID_ORDER_STATUS = 'payment/cointopay_gateway/order_status_paid';

    /**
     * API URL
     **/
    protected const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * @var $response
     **/
    protected $response = [] ;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_context      = $context;
        $this->scopeConfig   = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_coreSession  = $coreSession;
        $this->_curl         = $curl;
        $this->_resultJsonFactory = $resultJsonFactory;
    }//end __construct()

    /**
     * @inheritdoc
     */
    public function getCoin($param)
    {

        $storeScope         = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->coinId       = $param;
        $this->merchantId   = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->securityKey  = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope);
        $this->paidStatus   = $this->scopeConfig->getValue(self::XML_PATH_PAID_ORDER_STATUS, $storeScope);
        $this->currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

        return 'coindid '.$this->coinId;
    }//end getCoin()

    /**
     * @inheritdoc
     */
    private function verifyOrder()
    {
        $this->orderTotal = $this->getCartAmount();
        $ctp_api_url = 'https://cointopay.com/MerchantAPI?Checkout=true';
        $ctp_api_req = '&MerchantID='.$this->merchantId.'&Amount='.$this->orderTotal.'&AltCoinID='.$this->coinId;
        $ctp_api_req_a = '&CustomerReferenceNr='.$orderId.'&SecurityCode='.$this->securityKey.'&output=json';
        $ctp_api_req_b = '&inputCurrency='.$this->currencyCode.'&testcheckout';
        $this->_curlUrl = $ctp_api_url.$ctp_api_req.$ctp_api_req_a.$ctp_api_req_b;
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        if ($response == '"testcheckout success"') {
            return 'success';
        }

        return $response;
    }//end verifyOrder()

    /**
     * @inheritdoc
     */
    private function getCartAmount()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart          = $objectManager->get(\Magento\Checkout\Model\Cart::class);
        return $cart->getQuote()->getGrandTotal();
    }//end getCartAmount()
}//end class
