<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;

class checkoutController extends Controller
{
    //

    public function checkout()
    {
        $client = $this->getPaypalClient();
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "test_ref_id1",
                "amount" => [
                    "value" => "100.00",
                    "currency_code" => "USD"
                ]
            ]],
            "application_context" => [
                "cancel_url" => url(route('paypalCancel')),
                "return_url" => url(route('paypalReturn')),
            ]
        ];

        try {
            // Call API with your client and get a response for your call

            $response = $client->execute($request);
            // dd($response->result->links);
            if ($response->statusCode == 201) {
                session()->put('paypal_order_id', $response->result->id);
                foreach ($response->result->links as $link) {
                    if ($link->rel == 'approve') {
                        return redirect()->away($link->href);
                    }
                }
            }
            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            // 
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    protected function getPaypalClient()
    {
        $config = config('services.paypal');
        $environment = new SandboxEnvironment($config['client_id'], $config['secret']);
        $client = new PayPalHttpClient($environment);
        return $client;
    }

    public function paypalReturn()
    {
        $client = $this->getPaypalClient();
        $order_id = session()->get('paypal_order_id');
        $request = new OrdersCaptureRequest($order_id);
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $client->execute($request);

            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            dd($response);
            /*

 this will store my information in database

            */
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
        # code...
    }
    public function paypalCancel()
    {
        # code...
    }
}