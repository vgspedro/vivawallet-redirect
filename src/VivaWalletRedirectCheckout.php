<?php
namespace VgsPedro\VivaWalletRedirectCheckout;

class VivaWalletRedirectCheckout
{
	private $test_mode; // Boolean
	private $client_id; // Client ID, Provided by wallet
	private $client_secret; // Client Secret, Provided by wallet
	private $url; // Url to make request, sandbox or live (sandbox APP_ENV=dev or test) (live APP_ENV=prod)
	private $merchant_id; //Merchant ID , Provided by wallet
	private $api_key; //Api Key, Provided by wallet
	private $headers; //Set the authorization to curl

    public function __construct(){
    	$this->test_mode = false;
    	$this->client_id = '344whr50vw7hyxybr2fpwrmifsczt60j3hni4yww90ow8.apps.vivapayments.com';
    	$this->client_secret = '13CCNi1UpUYfj49w2nM2gm8e90E62W';
    	$this->merchant_id = 'b329d737-dbb9-4115-8dce-91c89b852bf3';
    	$this->api_key = '.@|!vO';
    	$this->url = 'https://demo.vivapayments.com';
    	$this->headers = [];
    	$this->headers[] = 'Authorization: Basic '.base64_encode($this->merchant_id.':'.$this->api_key);
    	$this->headers[] = 'Content-Type: application/json';
    }

    /**
    Every payment on the Viva Wallet platform needs an associated payment order.
    A payment order is represented by a unique numeric orderCode.
	$p_o[
		'client_email' => 'client@mail.com',
		'client_phone' => '+351963963963',
		'client_fullname' => 'Client Name ',
		'payment_timeout' => 86400, // Limit the payment period
		'invoice_lang' => 'pt-PT', // The invoice lang that the client sees
		'max_installments' => 0,
		'allow_recurring' => true,
		'is_preauth' => false,  // false captures the amount, true waits to be captured manually on wallet
		'amount' => 675, // int value, 1 euro is 100
		'merchant_trns' => 'Booking:45646',
		'customer_trns' => 'Reserva #45645 '
	]
	**/
	
	/**
	 * Set PaymentOrder
	 *
	 * @param array $p_o
	 *
	 * @return array
	 */
	public function setPaymentOrderRedirect(array $p_o){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url.'/api/orders');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		'{
			"Email": "'.$p_o['client_email'].'",
			"Phone": "'.$p_o['client_phone'].'",
			"FullName": "'.$p_o['client_fullname'].'",
			"PaymentTimeOut": '.$p_o['payment_timeout'].',
			"RequestLang": "'.$p_o['invoice_lang'].'",
			"MaxInstallments": '.$p_o['max_installments'].',
			"AllowRecurring": '.$p_o['allow_recurring'].',
			"IsPreAuth": '.$p_o['is_preauth'].',
			"Amount": '.$p_o['amount'].',
			"MerchantTrns":"'.$p_o['merchant_trns'].'",
			"CustomerTrns":"'.$p_o['customer_trns'].'"
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
	        'redirect_url' => $this->url.'/web/checkout?ref='.$e->OrderCode
	    ];
	}

	 /**
	 * A payment order is represented by a unique numeric
	 * Get Transaction
	 *
	 * @param string $transaction_id
	 *
	 * @return array
	 */
	public function getTransaction(string $transaction_id){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_URL, $this->url.'/api/transactions/'.$transaction_id);
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
