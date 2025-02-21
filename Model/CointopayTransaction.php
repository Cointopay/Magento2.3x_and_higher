<?php
namespace Cointopay\PaymentGateway\Model;

class CointopayTransaction
{

     /**
      * Context variable
      *
      * @var Context
      */
    protected $context;

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
    protected const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_api_key';

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
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     * @param \Magento\Framework\Json\DecoderInterface $decoder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Json\DecoderInterface $decoder
    ) {
        $this->context      = $context;
        $this->scopeConfig   = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_coreSession  = $coreSession;
        $this->_curl         = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_jsonEncoder      = $encoder;
        $this->_jsonDecoder      = $decoder;
    }//end __construct()

    /**
     * Get Transaction Details
     *
     * @param int $id
     * @return array
     */
    public function getTransactions($id)
    {

        $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager     = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store            = $storeManager->getStore();
        $baseUrl          = $store->getBaseUrl();
        $storeScope       = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $this->merchantKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
        $ctp_api_url = 'https://app.cointopay.com/v2REAPI?Call=Transactiondetail';
        $ctp_api_req_a = '&MerchantID='.$this->merchantId.'&APIKey='.$this->merchantKey;
        $ctp_api_req_b = '&TransactionID='.$id.'&output=json';
        $this->_curlUrl = $ctp_api_url.$ctp_api_req_a.$ctp_api_req_b;
        $this->_curl->get($this->_curlUrl);
        $response = $this->_jsonDecoder->decode($this->_curl->getBody());
        return [$response];
    }//end getTransactions()
}//end class
