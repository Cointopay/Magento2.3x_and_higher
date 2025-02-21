<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class VoidRequest implements BuilderInterface
{

    /**
     * Define config variable
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Processes
     *
     * @param ConfigInterface $config Config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }//end __construct()

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

        $order   = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        if ($payment instanceof OrderPaymentInterface === false) {
            throw new \LogicException('Order payment should be provided.');
        }

        return [
            'TXN_TYPE'     => 'V',
            'TXN_ID'       => $payment->getLastTransId(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            ),
        ];
    }//end build()
}//end class
