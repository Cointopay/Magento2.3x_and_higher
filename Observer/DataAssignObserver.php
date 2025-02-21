<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{

    /**
     * Assign Data event handler.
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data   = $this->readDataArgument($observer);

        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByKey('additional_data') !== null) {
            if (array_key_exists('transaction_result', $data->getDataByKey('additional_data'))) {
                $paymentInfo->setAdditionalInformation(
                    'transaction_result',
                    $data->getDataByKey('additional_data')['transaction_result']
                );
            }
        }
    }//end execute()
}//end class
