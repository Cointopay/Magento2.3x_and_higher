<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Cointopay\PaymentGateway\Gateway\Http\Client\ClientMock;

class ResponseCodeValidator extends AbstractValidator
{
    protected const RESULT_CODE = 'RESULT_CODE';

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject Subject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (isset($validationSubject['response']) === false || is_array($validationSubject['response']) !== true) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response) === true) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }//end validate()

    /**
     * Checking Transaction response
     *
     * @param array $response Response
     *
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        return isset($response[self::RESULT_CODE])
        && $response[self::RESULT_CODE] !== ClientMock::FAILURE;
    }//end isSuccessfulTransaction()
}//end class
