<?php
namespace Cointopay\PaymentGateway\Api;

interface CointopayTransactionInterface
{

    /**
     * GET for Transactions api
     *
     * @param  int $id
     * @return string
     */
    public function getTransactions($id);
}//end interface
