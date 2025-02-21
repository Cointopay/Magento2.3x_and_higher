<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Controller\Order;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class Index extends \Magento\Framework\App\Action\Action
{

     /**
      * Context variable
      *
      * @var Context
      */
    protected $context;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

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
     * @var $paidnenoughS
     **/
    protected $paidnenoughS;

    /**
     * @var $paidStatus
     **/
    protected $paidStatus;

    /**
     * @var $failedStatus
     **/
    protected $failedStatus;

    /**
     * @var $invoice
     **/
    protected $invoice;

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
    protected const XML_PATH_PAID_NOTENOUGH_ORDER_STATUS = 'payment/cointopay_gateway/order_status_paid_notenough';

    /**
     * Merchant COINTOPAY SECURITY Key
     */
    protected const XML_PATH_PAID_ORDER_STATUS = 'payment/cointopay_gateway/order_status_paid';

    /**
     * Merchant FAILED Order Status
     */
    protected const XML_PATH_ORDER_STATUS_FAILED = 'payment/cointopay_gateway/order_status_failed';

    /**
     * API URL
     **/
    public const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * @var $response
     **/
    protected $response = [] ;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\Raw $resultJsonFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->context      = $context;
        $this->_jsonEncoder  = $encoder;
        $this->_curl         = $curl;
        $this->scopeConfig   = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_pageFactory  = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultFactory     = $resultFactory;
        $this->orderManagement   = $orderManagement;
        $this->invoiceSender     = $invoiceSender;
        parent::__construct($context);
    }//end __construct()

    /**
     * @inheritdoc
     **/
    public function execute()
    {
        $page = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        try {
            $customerReferenceNr = $this->getRequest()->getParam('CustomerReferenceNr');
            $status            = $this->getRequest()->getParam('status');
            $ConfirmCode       = $this->getRequest()->getParam('ConfirmCode');
            $SecurityCode      = $this->getRequest()->getParam('SecurityCode');
            $notenough         = $this->getRequest()->getParam('notenough');
            $storeScope        = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->securityKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_SECURITY, $storeScope);
            $this->paidnenoughS = $this->scopeConfig->getValue(self::XML_PATH_PAID_NOTENOUGH_ORDER_STATUS, $storeScope);
            $this->paidStatus          = $this->scopeConfig->getValue(self::XML_PATH_PAID_ORDER_STATUS, $storeScope);
            $this->failedStatus        = $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_FAILED, $storeScope);
            
            if ($this->securityKey == $SecurityCode) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order         = $objectManager->create(\Magento\Sales\Model\Order::class)
                    ->loadByIncrementId($customerReferenceNr);
                if (count($order->getData()) > 0) {
                    if ($status == 'paid' && $notenough == 1) {
                        $order->setState($this->paidnenoughS)->setStatus($this->paidnenoughS);
                        $order->save();
                        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                        $blockMsg = 'Not enough was received, please pay the remaining amount or contact support';
                        $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                            ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                            ->setData('message', $blockMsg)
                            ->toHtml();
                        $result->setContents($block);
                        // return $result;
                        // $result->setData([$block]);
                        return $result;
                    } elseif ($status == 'paid') {
                        if ($order->canInvoice()) {
                               $this->invoice = $order->prepareInvoice();
                               $this->invoice->getOrder()->setIsInProcess(true);
                               $this->invoice->register()->pay();
                               $this->invoice->save();
                        }

                        $order->setState($this->paidStatus)->setStatus($this->paidStatus);
                        $order->save();
                        if ($order->canInvoice()) {
                            $this->invoiceSender->send($this->invoice);
                        }
                    } elseif ($status == 'failed') {
                        if ($order->getStatus() == 'complete') {
                            $rest = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                            $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                                ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                                ->setData('message', 'Order cannot be cancel now, because it is completed now.')
                                ->toHtml();
                            $rest->setContents($block);
                            return $rest;
                        } else {
                           // $this->orderManagement->cancel($order->getId());
                            $order->setState($this->failedStatus)->setStatus($this->failedStatus);
                            $order->save();
                            $relt = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                            $block = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                               ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                               ->setData('message', 'Order successfully cancelled.')
                               ->toHtml();
                            $relt->setContents($block);
                            return $relt;
                        }//end if
                    } else {
                        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                        $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                            ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                            ->setData('message', 'Order status should have valid value.')
                            ->toHtml();
                        $result->setContents($block);
                        return $result;
                    }//end if
                    $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                    $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                        ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                        ->setData('message', 'Order status successfully updated.')
                        ->toHtml();
                    $result->setContents($block);
                    return $result;
                } else {
                    $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                    $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                    ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                    ->setData('message', 'Order status successfully updated.')
                    ->toHtml();
                    $result->setContents($block);
                    return $result;
                }//end if
            } else {
                $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                ->setData('message', 'Order status successfully updated.')
                ->toHtml();
                $result->setContents($block);
                return $result;
            }//end if
        } catch (\Exception $e) {
            $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
            $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
                ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
                ->setData('message', 'General error:'.$e->getMessage())
                ->toHtml();
            $result->setContents($block);
            return $result;
        }//end try
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $block  = $page->getLayout()->createBlock(\Cointopay\PaymentGateway\Block\Index::class)
            ->setTemplate('Cointopay_PaymentGateway::order_response.phtml')
            ->setData('message', 'Something went wrong. Try again later')
            ->toHtml();
        $result->setContents($block);
        return $result;
    }//end execute()
}//end class
