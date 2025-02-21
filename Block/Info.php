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
use Magento\Framework\Escaper;

class Info extends ConfigurableInfo
{
    
    /**
     * @var $escaper
     **/
    protected $escaper;
    
    /**
     * Returns label
     *
     * @param string $field Label
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
     * @param string $field Filed
     * @param string $value Value
     *
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        switch ($field) {
            case FraudHandler::FRAUD_MSG_LIST:
                return implode('; ', $value);
        }

        return parent::getValueView($field, $value);
    }//end getValueView()
    
    /**
     * @inheritdoc
     */
    public function getobjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}//end class
