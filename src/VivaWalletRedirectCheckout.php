<?php
namespace VgsPedro\VivaWalletRedirectCheckout;

class VivaWalletRedirectCheckout
{

    /**
    Every payment on the Viva Wallet platform needs an associated payment order.
    A payment order is represented by a unique numeric orderCode.

    PaymentOrder data struture
	
	$po[
		'client_email' => 'client@mail.com', //string
		'client_phone' => '+351963963963', //string
		'client_fullname' => 'Client Name ', //string
		'payment_timeout' => 86400, // int Limit the payment period
		'invoice_lang' => 'pt-PT', //string  The invoice lang that the client sees
		'max_installments' => 0, //int
		'allow_recurring' => true, // Boolean
		'is_preauth' => false,  // Boolean false captures the amount, true waits to be captured manually on wallet
		'amount' => 675, // int value, 1 euro is 100
		'merchant_trns' => 'Booking:45646', // string
		'customer_trns' => 'Reserva #45645 ' // string
 	]
	**/
	
	/**
	 * Set PaymentOrder
	 * @param array $po PaymentOrder
	 * @param array $c Credencials
	 * @return array
	 */
	public function setPaymentOrderRedirect(array $c = [], array $po = []){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $c['url'].'/api/orders');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $c['headers']);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		'{
			"Email": "'.$po['client_email'].'",
			"Phone": "'.$po['client_phone'].'",
			"FullName": "'.$po['client_fullname'].'",
			"PaymentTimeOut": '.$po['payment_timeout'].',
			"RequestLang": "'.$po['invoice_lang'].'",
			"MaxInstallments": '.$po['max_installments'].',
			"AllowRecurring": '.$po['allow_recurring'].',
			"IsPreAuth": '.$po['is_preauth'].',
			"Amount": '.$po['amount'].',
			"MerchantTrns":"'.$po['merchant_trns'].'",
			"CustomerTrns":"'.$po['customer_trns'].'"
		}');

		if (curl_errno($ch)){
			$err = curl_error($ch);
			curl_close($ch);

		   	return [
		   		'status' => 0,
		   		'data' => $err,
		   		'redirect_url' => null
		   	];
		}

		$result = curl_exec($ch);
		curl_close($ch);
 		$e = json_decode($result);

	  	return [
	  		'status' => 1,
	        'data' => $result,
	        'redirect_url' => $c['url'].'/web/checkout?ref='.$e->OrderCode
	    ];
	}

	 /**
	 * A payment order is represented by a unique numeric
	 * Get Transaction
	 * @param string $transaction_id
	 * @param array $c Credencials
	 * @return array
	 */
	public function getTransaction(array $c = [], string $transaction_id = null){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $c['headers']);
		curl_setopt($ch, CURLOPT_URL, $c['url'].'/api/transactions/'.$transaction_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

		if (curl_errno($ch)){

			$err = curl_error($ch);
			curl_close($ch);
		   	return [
		   		'status' => 0,
		   		'data' => $err,
		   	];
		}
		$result = curl_exec($ch); 
		curl_close($ch);
 		$e = json_decode($result);

		return [
	  		'status' => 1,
	        'data' => $e//$result
	    ];

	}
}
