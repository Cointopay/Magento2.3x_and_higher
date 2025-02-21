<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Controller\Coin;

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
     * ScopeConfigInterface variable
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

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
    protected $curlUrl;

    /**
     * @var currencyCode
     **/
    protected $currencyCode;

    /**
     * StoreManagerInterface variable
     *
     * @var $_storeManager
     **/
    protected $storeManager;

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
     * API URL
     **/
    protected const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * Response array
     *
     * @var $response
     **/
    protected $response = [] ;

    /**
     * Registry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->context      = $context;
        $this->jsonEncoder  = $encoder;
        $this->curl         = $curl;
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->pageFactory  = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->coreSession       = $coreSession;
        $this->registry          = $registry;
        parent::__construct($context);
    }//end __construct()

    /**
     * Execute the action
     *
     * @return void
     */
    public function execute()
    {
        $cCoinId = $this->getRequest()->getParam('coinId');
        $this->coreSession->start();
        $this->coreSession->setCoinId($cCoinId);
        return $this->getResponse()->setBody($cCoinId);
    }//end execute()
}//end class
