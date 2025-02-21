<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class ClientMock implements ClientInterface
{
    public const SUCCESS = 1;
    public const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE,
    ];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }//end __construct()

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param  TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );

        $this->logger->debug(
            [
                'request'  => $transferObject->getBody(),
                'response' => $response,
            ]
        );

        return $response;
    }//end placeRequest()

    /**
     * Generates response
     *
     * @param int $resultCode
     * @return array
     */
    protected function generateResponseForCode($resultCode)
    {

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID'      => $this->generateTxnId(),
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }//end generateResponseForCode()

    /**
     * Generates Hash
     *
     * @return string
     */
    protected function generateTxnId()
    {
        return hash('sha256', random_int(0, 1000));
    }//end generateTxnId()

    /**
     * Returns result code
     *
     * @param  TransferInterface $transfer
     * @return int
     */
    private function getResultCode(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int) $headers['force_result'];
        }

        return $this->results[random_int(0, 1)];
    }//end getResultCode()

    /**
     * Returns response fields for result code
     *
     * @param  int $resultCode
     * @return array
     */
    private function getFieldsBasedOnResponseType($resultCode)
    {
        switch ($resultCode) {
            case self::FAILURE:
                return [
                'FRAUD_MSG_LIST' => [
                    'Stolen card',
                    'Customer location differs',
                ],
            ];
        }

        return [];
    }//end getFieldsBasedOnResponseType()
}//end class
