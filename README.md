# Viva Wallet Redirect Payment

## How to use

This library is installed via [Composer](http://getcomposer.org/).

composer require vgspedro/vivawalletredirectcheckout:dev-master

## Symfony framework

#### Create Routes

# config/routes.yaml

```
payment:
    path: /admin/payment
    controller: App\Controller\PaymentController::index

payment_status:
    path: /payment-status/{status}
    controller: App\Controller\PaymentController::status
```

#### Create the Controler

# src/Controler/Payment.php

```php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\VivaWalletRedirect;

class PaymentController extends AbstractController
{
    private $environment;
    
    public function __construct(ParameterBagInterface $environment)
    {
        $this->environment = $environment;
    }

    public function index(VivaWalletRedirect $checkout)
    {

        $checkoutVivaWalletRedirectCheckout();

        $p_o = [
            'client_email' => 'client@email.com',
            'client_phone' => '+351963963963',
            'client_fullname' => 'Client Name',
            'payment_timeout' => 86400, // Limit the payment period
            'invoice_lang' => 'pt-PT', // The invoice lang that the client sees
            'max_installments' => 0,
            'allow_recurring' => true,
            'is_preauth' => false, // false captures the amount, true waits to be captured manually on wallet
            'amount' => 675,  // int value, 1 euro is 100,
            'merchant_trns' => 'Booking:45646',
            'customer_trns' => 'Reserva #45645'
        ];

        return $this->render('admin/payment/list.html', [
            'redirect_url' => $checkout->setPaymentOrderRedirect($p_o),
            'payment_url' => $this->environment->get("kernel.environment") == 'prod' ? 'https://www.vivapayments.com' : 'https://demo-api.vivapayments.com',
        ]);
    }


    public function status($status = null, Request $request, VivaWalletRedirect $checkout)
    {       
        //$request->query->get('s'); //Order code
        //$request->query->get('t'); //Transaction ID
        //$request->query->get('lang'); // Locale

       if($status == 'fail' && $request->query->get('eventId'))
            return $this->render('admin/payment/fail.html', [
                'transaction' => 'Failed'
            ]);

        if ($status == 'success' && $request->query->get('t'))

            return $this->render('admin/payment/success.html', [
                'transaction' => $checkout->getTransaction($request->query->get('t')),
            ]);
        
        return $this->render('admin/payment/fail.html', [
            'transaction' => 'Not processed'
        ]);
    }

}

```

#### Create the Templates

# templates/admin/payment/list.html
```html
  <div class="container pt-4 text-center">
    <span>Card Number</span><br>
    <input type="text" size="20" name="txtCardNumber" autocomplete="off" data-vp="cardnumber" value="4111111111111111" />
    <br>
    <br>
    <a class="btn btn-info" href="{{ redirect_url.redirect_url }}" target="_blank">Redirect Pay now</a>
  </div>
```

# templates/admin/payment/success.html

```html
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Success Page</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/admin/payment">Home</a></li>
              <li class="breadcrumb-item active">Success</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="error-page">
        <h2 class="headline text-success">Success</h2>

        <div class="error-content">
          <h3><i class="fas fa-check text-success"></i> Payment Successful.</h3>

          <p>
            You may <a href="/admin/payment">return to Payment</a>.
          </p>

        </div>

      </div>       
    </section>
    <div class="pt-4 mt-4">
     {{dump(transaction)}}
    </div>
  </div>
```

# templates/admin/payment/fail.html

```html
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{transaction}} Page</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <a href="/admin/payment">Home</a>
              </li>
              <li class="breadcrumb-item active">{{transaction}} Page</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="error-page">
        <h2 class="headline text-danger">{{transaction}}</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Something went wrong. <br> Transaction {{transaction}}!</h3>
          <p>
            Meanwhile, you may <a href="/admin/payment">return to payment</a>.
          </p>
        </div>
      </div>
    </section>
  </div>
```

#### Create the Service

# src/Service/VivaWalletRedirect.php

```php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use \VgsPedro\VivaWalletRedirecCheckout\VivaWalletRedirecCheckout;

class VivaWalletRedirect
{

  private $client_id; //string required - Client ID, Provided by wallet
  private $client_secret; // string required - Client Secret, Provided by wallet
  private $url; //string required - Url to make request, sandbox or live (sandbox APP_ENV=dev or test) (live APP_ENV=prod)
  private $merchant_id; //string required - Merchant ID , Provided by wallet
  private $api_key; //string required - Api Key, Provided by wallet
  private $headers; //Set the authorization to curl

    public function __construct(){
      $this->client_id = '344whr50vw7hyxybr2fpwrmifsczt60j3hni4yww90ow8.apps.vivapayments.com';
      $this->client_secret = '13CCNi1UpUYfj49w2nM2gm8e90E62W';
      $this->merchant_id = 'b329d737-dbb9-4115-8dce-91c89b852bf3';
      $this->api_key = '.@|!vO';
      $this->url = $environment->get("kernel.environment") == 'prod' ? 'https://www.vivapayments.com' : 'https://demo.vivapayments.com';
      $this->headers = [];
      $this->headers[] = 'Authorization: Basic '.base64_encode($this->merchant_id.':'.$this->api_key);
      $this->headers[] = 'Content-Type: application/json';
    }

  /**
   * Set PaymentOrder
   * @param array $po PaymentOrder
   * @param array $c Credencials
   * @return array
   */
  public function setPaymentOrderRedirect(array $po = []){
    return (new VivaWalletRedirecCheckout())->setPaymentOrderRedirect($this->headers, $po);
  }

   /**
   * A payment order is represented by a unique numeric
   * Get Transaction
   * @param string $transaction_id
   * @return array
   */
  public function getTransaction(string $transaction_id = null){
    return (new VivaWalletRedirecCheckout())->getTransaction($this->headers, $transaction_id);
  }

}

```