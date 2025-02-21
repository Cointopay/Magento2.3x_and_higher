<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Cointopay\PaymentGateway\Gateway\Http\Client\ClientMock;

class MockDataRequest implements BuilderInterface
{
    public const FORCE_RESULT = 'FORCE_RESULT';

    /**
     * Builds ENV request
     *
     * @param array $buildSubject Subject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (isset($buildSubject['payment']) === false
            || $buildSubject['payment'] === false instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $buildSubject['payment'];
        $payment   = $paymentDO->getPayment();

        $transactionResult = $payment->getAdditionalInformation('transaction_result');
        $result            = $transactionResult;
        if ($transactionResult === null) {
            $result = ClientMock::SUCCESS;
        }

        return [self::FORCE_RESULT => $result];
    }//end build()
}//end class
