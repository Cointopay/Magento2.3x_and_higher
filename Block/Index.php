<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Block;

class Index extends \Magento\Framework\View\Element\Template
{
    public function getOrderOutput(){
        return $this->getMessage();
    }

}