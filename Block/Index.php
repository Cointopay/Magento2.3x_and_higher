<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author  cointopay <info@cointopay.com>
 * @license See COPYING.txt for license details.
 */

namespace Cointopay\PaymentGateway\Block;

class Index extends \Magento\Framework\View\Element\Template
{
    
    /**
     * Returns Message
     *
     * @return string
     */
    public function getOrderOutput()
    {
        return $this->getMessage();
    }//end getOrderOutput()
    
    /**
     * @inheritdoc
     */
    public function getobjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
}//end class
