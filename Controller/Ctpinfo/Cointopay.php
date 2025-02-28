<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Controller\Ctpinfo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Cointopay Payment Controller
 */
class Cointopay extends \Magento\Framework\App\Action\Action
{

    /** @var \Magento\Framework\App\Action\Context */
    protected $context;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultJsonFactory;

    /** @var $_resultOutput */
    protected $_resultOutput;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $requestRepo;

    /** @var \Magento\Framework\View\Asset\Repository */
    protected $assetRepo;

    /**
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
    public const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';

    /**
     * Merchant COINTOPAY API Key
     */
    public const XML_PATH_MERCHANT_KEY = 'payment/cointopay_gateway/merchant_gateway_api_key';

    /**
     * Merchant COINTOPAY SECURITY Key
     */
    public const XML_PATH_MERCHANT_SECURITY = 'payment/cointopay_gateway/merchant_gateway_security';

    /**
     * API URL
     **/
    public const COIN_TO_PAY_API = 'https://cointopay.com/MerchantAPI';

    /**
     * @var $response
     **/
    protected $response = [] ;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    /**
     * @var $fileDriver
     */
    protected $fileDriver;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\RequestInterface $requestRepo
     * @param File $fileDriver
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $requestRepo,
        File $fileDriver
    ) {
        $this->context     = $context;
        $this->jsonEncoder = $jsonEncoder;
        $this->curl        = $curl;
        $this->scopeConfig  = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->requestRepo      = $requestRepo;
        $this->assetRepo        = $assetRepo;
        $this->fileDriver = $fileDriver;
        parent::__construct($context);
    }//end __construct()

    /**
     * Execute method
     *
     * @return ResponseInterface
     */
    public function execute()
    {

        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('selected_transaction_id')) {
            $objectManager    = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager     = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            $store            = $storeManager->getStore();
            $baseUrl          = $store->getBaseUrl();
            $storeScope       = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transactionId    = $this->getRequest()->getParam('selected_transaction_id');
            $this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
            $this->merchantKey = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_KEY, $storeScope);
            $cointopay_req_a = 'https://app.cointopay.com/v2REAPI?Call=Transactiondetail';
            $cointopay_req_b = '&MerchantID='.$this->merchantId.'&APIKey='.$this->merchantKey;
            $cointopay_req_c = '&TransactionID='.$transactionId.'&output=json';
            $this->_curlUrl    = $cointopay_req_a.$cointopay_req_b.$cointopay_req_c;
            $this->curl->get($this->_curlUrl);
            $response_body = $this->curl->getBody();
            $result        = json_decode($response_body);
            $result_ctp    = $this->resultJsonFactory->create();
            if ($result) {
                if ($result->status_code == 200) {
                       $response            = $result->data;
                       $this->_resultOutput = '';
                       $params = ['_secure' => $this->requestRepo->isSecure()];
                       $getUrl = $objectManager->get(\Magento\Framework\View\Element\Template::class);
                       $this->_resultOutput  .= '<div class="popup-overlay cointopay active">
					<div class="popup-content cointopay active">    
						<img src="'
                        .$this->assetRepo->getUrlWithParams('Cointopay_PaymentGateway::images/cointopay.gif', $params)
                        .'" />
						<p class="description" style="text-align:center !important;">
						<strong>Please make the payment and wait for the success confirmation page.</strong></p>
						<h1> PAYMENT DETAILS </h1>
						<div class="cointopay_details_main">
						<div class="cointopay_details_qrcode">';
                        $this->_resultOutput .= '<img src="'.$response->QRCodeURL
                        .'" alt="Cointopay Transaction details are in progress please wait." 
						title="QR Scan Cointopay" class="ctpQRcode" width="" />';
                        $ctpurlcoin = 'https://quickchart.io/qr?size=300&text='.$response->coinAddress.', r';
                    if ($this->fileDriver->fileOpen($ctpurlcoin, 'r')) {
                        $this->_resultOutput .= '<img src="'
                        .'https://quickchart.io/qr?size=300&text='.$response->coinAddress.'" 
						alt="ctpCoinAdress" class="ctpCoinAdress" title="coinAddress" style="display:none;" width="" />';
                    }

                        $this->_resultOutput .= '</div>
						<div class="cointopay_details">
							<p class="remaining_amount"><strong>Amount:</strong><br>
							   '.$response->Amount.' '.'
								'.strtoupper($response->CoinName).' '.'
								<img src="'
                                .'https://s3-eu-west-1.amazonaws.com/cointopay/img/'.
                                $response->CoinName.'_dash2.png'.'" style="width:20px;" />
							</p>';
                    if (property_exists($response, 'Tag')) {
                        if (!empty($response->Tag)) {
                            $this->_resultOutput .= '<p class="description"><strong>Memo/Tag: </strong> '
                            .$response->Tag.' </p>';
                        }
                    }

                            $this->_resultOutput .= '<p class="address"><strong>Address: </strong> 
							<br> <input type="text" value="'.$response->coinAddress.'" style="width: 100%;" /> </p>
							<p class="description"><button class="btn btn-success btnCrypto mb-2">CRYPTO LINK</button></p>
							<p class="time"><strong>Expiry: </strong> <span id="expire_time">'
                            .date("m/d/Y h:i:s T", strtotime("+".$response->ExpiryTime." minutes")).'</span></p>
							<p class="trxid"><strong>Transaction ID: </strong> '.$response->TransactionID.'</p>
							<p class="description">Make sure to send enough to cover  any coin transaction fees!</p>
							<p class="description">Send an equal amount or more.</p>
							 <p class="description"> <a target="_blank" href="'.$response->RedirectURL.'">View Invoice details</a></p>
							<input type="hidden" id="cointopay_trid" value="'.$response->TransactionID.'" />
						</div>
						</div>
					</div>
					</div>
					<script type="text/javascript">
						require(
							[\'jquery\'],
							function($) {
								$(function() {
									interval = setInterval(function() {
										if ($("#cointopay_trid").length) {
											selected_coin = $("#cointopay_trid").val();
											$.ajax ({
												url: "'.$getUrl->getUrl("paymentcointopay").'",
												//showLoader: true,
												data: {paymentaction:selected_coin, type:\'status\'},
												type: "POST",
												success: function(result) {
													if (result.status == \'paid\') {
														$(\'.popup-content.cointopay\').html(\'<h3>Thank you, your payment is received!
														</h3><p class="description"> <a target="_blank" href="'
                                                        .$response->RedirectURL.'">View Invoice details</a></p>\');
													} else if (result.status == \'expired\') {
														$(\'.popup-content.cointopay\').html(\'<h3>Payment time expired!</h3>
														<p class="description"> <a target="_blank" href="'.$response->RedirectURL.'">
														View Invoice details</a></p>\');
													}
													 else if (result.status == \'underpaid\') {
														$(\'.popup-content.cointopay\').html(\'
														<h3>You have underpaid, please pay the remaining amount or contact support!</h3>
														<p class="description"> <a target="_blank" href="'.$response->RedirectURL.'">
														View Invoice details</a></p>\');
													}
												}
											});
										}
									}, 10000);
									
									// count down time left
									var d1 = new Date (),
									d2 = new Date ( d1 );
									d2.setMinutes ( d1.getMinutes() + '.$response->ExpiryTime.' );
									var countDownDate = d2.getTime();

									// Update the count down every 1 second
									var x = setInterval(function() {
										if ($(\'#expire_time\').length) {
											// Get todays date and time
											var now = new Date().getTime();
											
											// Find the distance between now an the count down date
											var distance = countDownDate - now;
											
											// Time calculations for days, hours, minutes and seconds
											var days = Math.floor(distance / (1000 * 60 * 60 * 24));
											var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
											var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
											var seconds = Math.floor((distance % (1000 * 60)) / 1000);
											
											// Output the result in an element with id="expire_time"
											document.getElementById("expire_time").innerHTML = days + "d " + hours + "h "
											+ minutes + "m " + seconds + "s ";
											
											// If the count down is over, write some text 
											if (distance < 0) {
												clearInterval(x);
												document.getElementById("expire_time").innerHTML = "EXPIRED";
											}
										}
									}, 1000);
									if ($(\'.btnCrypto\').length) {
										$(\'.btnCrypto\').click(function(){
											if ($(this).text() == \'CRYPTO LINK\') {
												$(this).text(\'CRYPTO ADDRESS\');
												$(\'.ctpQRcode\').hide();
												$(\'.ctpCoinAdress\').show();
											} else if ($(this).text() == \'CRYPTO ADDRESS\') {
												$(this).text(\'CRYPTO LINK\');
												$(\'.ctpCoinAdress\').hide();
												$(\'.ctpQRcode\').show();
											}
											
										});
									}
								});
							});
					</script>';
                    return $result_ctp->setData(["ctpData" => $this->_resultOutput]);
                } else {
                       return $result_ctp->setData(["ctpData" => $result->message]);
                }//end if
            } else {
                return $result_ctp->setData(["ctpData" => "No Cointopay data found due to empty session values"]);
            }//end if
        }//end if

        return false;
    }//end execute()
}//end class
