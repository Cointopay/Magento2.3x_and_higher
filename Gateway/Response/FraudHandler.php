<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class FraudHandler implements HandlerInterface
{
    protected const FRAUD_MSG_LIST = 'FRAUD_MSG_LIST';

    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject Subject
     * @param array $response        Response
     *
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (isset($response[self::FRAUD_MSG_LIST]) === false || is_array($response[self::FRAUD_MSG_LIST]) === false) {
            return;
        }

        if (isset($handlingSubject['payment']) === false
            || $handlingSubject['payment'] === false instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        $payment   = $paymentDO->getPayment();

        $payment->setAdditionalInformation(
            self::FRAUD_MSG_LIST,
            (array) $response[self::FRAUD_MSG_LIST]
        );

        $payment->setIsTransactionPending(true);
        $payment->setIsFraudDetected(true);
    }//end handle()
}//end class
