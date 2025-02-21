<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Cointopay\PaymentGateway\Gateway\Request\MockDataRequest;

class TransferFactory implements TransferFactoryInterface
{

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }//end __construct()

    /**
     * Builds gateway transfer object
     *
     * @param  array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $force_result = null;
        if (isset($request[MockDataRequest::FORCE_RESULT])) {
            $force_result = $request[MockDataRequest::FORCE_RESULT];
        }
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->setHeaders(
                [
                    'force_result' => $force_result,
                ]
            )
            ->build();
    }//end create()
}//end class
