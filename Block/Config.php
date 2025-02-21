<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Cointopay\PaymentGateway\Gateway\Response\FraudHandler;

class Config extends ConfigurableInfo
{
    
    /**
     * Returns label
     *
     * @param string $field Get Field Label
     *
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }//end getLabel()

    /**
     * Returns value view
     *
     * @return string | URL
     */
    public function getAjaxUrl()
    {
        return $this->getUrl("cointopaycoins");
    }//end getAjaxUrl()

    /**
     * Returns value view
     *
     * @return string | URL
     */
    public function getCoinsPaymentUrl()
    {
        return $this->getUrl("paymentcointopay");
    }//end getCoinsPaymentUrl()

    /**
     * Returns value view
     *
     * @param string $status Payment Status.
     *
     * @return string | Status
     */
    public function cointopayReference($status)
    {
        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get(Magento\Customer\Model\Session::class);
        if (isset($customerSession) === true && isset($status) === true) {
            return json_encode('cointopay_ref');
        }

        return false;
    }//end cointopayReference()
    
    /**
     * @inheritdoc
     */
    public function getobjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}//end class
