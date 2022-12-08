<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;
use Illuminate\Http\Request;

class StripeService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $key;

    protected $secret;

    protected $plans;

    public function __construct(){
        $this->baseUri      = config('services.stripe.base_uri');
        $this->key          = config('services.stripe.key');
        $this->secret       = config('services.stripe.secret');
        $this->plans        = config('services.stripe.plans');
    }

    public function resolveAuthorization(&$queryParams, &$headers, &$formParams)
    {   
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }

    public function resolveAccessToken()
    {
        return "Bearer {$this->secret}";
    }

    public function handleSubscription(Request $request)
    {
        $user = json_decode(session()->get('user'));

        $customer       = $this->createCustomer(
            $user->name . " " .$user->surname,
            $user->email,
            $request->payment_method
        );

        $subscription   = $this->createSubscription(
            $customer->id,
            $request->payment_method,
            $this->plans[$request->plan]
        );

        if($subscription->status == 'active'){
            session()->put('subscriptionId', $subscription->id);

            return redirect()->route('subscribe.approval',[
                'plan'              => $request->plan,
                'subscription_id'   => $subscription->id
            ]);
        }

        $paymentIntent          = $subscription->latest_invoice->payment_intent;

        if($paymentIntent->status === 'requires_action'){
            session()->put('subscriptionId', $subscription->id);

            $clientSecret           = $paymentIntent->client_secret;  

            return view('stripe.3d-secure-subscription', [
                'plan'              => $request->plan,
                'payment_method'    => $request->payment_method,
                'subscriptionId'    => $subscription->id,
                'clientSecret'      => $clientSecret
            ]);
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('We were unable to activate the subscription. Try Again, please.');
    }

    public function validateSubscription(Request $request)
    {
        if (session()->has('subscriptionId')) {
            $subscriptionId = session()->get('subscriptionId');
            session()->forget('subscriptionId');
            return $request->subscription_id == $subscriptionId;
        }

        return false;
    }

    /**
     * This function is to create a customer in stripe as its needed to create a subscription in stripe
     */
    public function createCustomer($name, $email, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            '/v1/customers',
            [
                'name'              => $name,
                'email'             => $email,
                'payment_method'    => $paymentMethod,
            ],
        );
    }

    // this function send request to stripe to create subscription
    public function createSubscription($customerId, $paymentMethod, $priceId)
    {
        return $this->makeRequest(
            'POST',
            '/v1/subscriptions',
            [
                'customer'                  => $customerId,
                'items'                     => [
                                                ['price' => $priceId]
                                            ],
                'default_payment_method'    => $paymentMethod,
                'expand'                    => ['latest_invoice.payment_intent']
            ]
        );
    }
}