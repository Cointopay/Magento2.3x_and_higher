<?php
namespace Cointopay\PaymentGateway\Block;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

class CtpInfo extends Template
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     *
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     *
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Processes the payment request.
     *
     * @param \Magento\Sales\Model\OrderFactory $orderFactory Creates order instances.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param LoggerInterface $logger
     * @param array $data = []
     **/
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, (array) $registry, $data);
        $this->orderFactory = $orderFactory;
        $this->scopeConfig   = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->curl         = $curl;
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->orderCollectionFactory   = $orderCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->customerSession           = $customerSession;
        $this->logger = $logger;
    }//end __construct()

    /**
     * Get Order
     *
     * @return array
     **/
    public function getOrder()
    {
        // get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;

        // get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;

        $orderCollection = $this->orderCollectionFactory->create($this->getCustomerId());

        $orderCollection->getSelect()->join(
            ["sop" => "sales_order_payment"],
            'main_table.entity_id = sop.parent_id',
            ['method']
        )->where('sop.method = ?', 'cointopay_gateway');

        $orderCollection->setOrder('entity_id', 'DESC');
        $orderCollection->setPageSize($pageSize);
        $orderCollection->setCurPage($page);
        return  $orderCollection;
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
        $this->logger->info('getCointopayHtml called');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager  = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store         = $storeManager->getStore();
        $baseUrl       = $store->getBaseUrl();
        $storeScope    = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $orderObj      = $this->getOrder();
        $payment_method_code = $orderObj->getPayment()->getMethodInstance()->getCode();
        if ($payment_method_code == 'cointopay_gateway') {
            if (null !== $orderObj->getExtOrderId()) {
                $this->transactionId = $orderObj->getExtOrderId();
                $this->merchantId    = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
                $this->merchantKey   = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
                $ctpapiurl = 'https://app.cointopay.com/v2REAPI?Call=Transactiondetail';
                $ctpapiurlp = '&MerchantID='.$this->merchantId.'&APIKey='.$this->merchantKey;
                $this->_curlUrl = $ctpapiurl.$ctpapiurlp.'&TransactionID='.$this->transactionId.'&output=json';
                $this->curl->get($this->_curlUrl);
                $response = $this->curl->getBody();
                return json_decode($response);
            }
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
        $storeManager  = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $store         = $storeManager->getStore();
        $baseUrl       = $store->getBaseUrl();
        $storeScope    = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $orderObj      = $this->getOrder();
        if (null !== $orderObj->getExtOrderId()) {
            $this->transactionId = $orderObj->getExtOrderId();
            $this->merchantId    = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
            $this->merchantKey   = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
            $cointopayapiurl = 'https://app.cointopay.com/v2REAPI?Call=Transactiondetail';
            $cointopayapiurlp = '&MerchantID='.$this->merchantId.'&APIKey='.$this->merchantKey;
            $this->_curlUrl = $cointopayapiurl.$cointopayapiurlp.'&TransactionID='.$this->transactionId.'&output=json';
            $this->curl->get($this->_curlUrl);
            $response = $this->curl->getBody();
            return json_decode($response);
        }

        return false;
    }//end getTransactions()

    /**
     * Page Layout
     *
     * @return bool
     **/
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Order'));

        if ($this->getOrder()) {
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class, 'cointopay.order.pager')
            ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15])->setShowPerPage(true)->setCollection(
                $this->getOrder()
            );
            $this->setChild('pager', $pager);

            $this->getOrder()->load();
        }

        return $this;
    }//end _prepareLayout()

    /**
     * Get Pager Html
     *
     * @return string
     **/
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }//end getPagerHtml()
    
    /**
     * @inheritdoc
     */
    public function getobjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}//end class
