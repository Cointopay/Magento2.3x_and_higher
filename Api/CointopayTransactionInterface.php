<?php 
namespace Cointopay\PaymentGateway\Api;
 
 
interface CointopayTransactionInterface {


	/**
	 * GET for Transactions api
	 * @param int $param
	 * @return string
	 */
	
	public function getTransactions($id);
}
