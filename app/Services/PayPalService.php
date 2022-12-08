<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;
use Illuminate\Http\Request;

class PayPalService
{
    use ConsumesExternalServices;

    protected $baseUri;

    protected $clientId;

    protected $clientSecret;

    protected $plans;

    public function __construct(){
        $this->baseUri      = config('services.paypal.base_uri');
        $this->clientId     = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->plans        = config('services.paypal.plans');
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
        $credentials = base64_encode("{$this->clientId}:{$this->clientSecret}");
        return "Basic {$credentials}";
    }

    public function handleSubscription(Request $request)
    {
        // create a subscription of our request data
        $user = json_decode(session()->get("user"));
        $subscription = $this->createSubscription(
            $request->plan,
            $user->name . " " . $user->surname,
            $user->email
        );
        $subscriptionLinks = collect($subscription->links);
        $approve = $subscriptionLinks->where('rel','approve')->first();
        session()->put('subscriptionId',$subscription->id);

        return redirect($approve->href);

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

    public function createSubscription($planSlug, $name, $email)
    {
        return $this->makeRequest(
            'POST',
            '/v1/billing/subscriptions',
            [],
            [
                'plan_id'       => $this->plans[$planSlug],
                'subscriber'    => [
                    'subscriber'    => [
                        'name'          => [
                            'given_name'     => $name
                        ],
                        'email_address' => $email
                    ]
                ],
                'application_context' => [
                    'brand_name'            => config('app.name'),
                    'shipping_preference'   => 'NO_SHIPPING',
                    'user_action'           => 'SUBSCRIBE_NOW',
                    'return_url'            => route('subscribe.approval', ['plan' => $planSlug]),
                    'cancel_url'            => route('subscribe.cancelled'),
                ]
            ],
            [],
            $isJsonRequest = true
        );
    }
}