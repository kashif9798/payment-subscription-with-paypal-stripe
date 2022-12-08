<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ConsumesExternalServices;
use App\Resolvers\PaymentPlatformResolver;

class SubscriptionController extends Controller
{
    use ConsumesExternalServices;

    protected $paymentPlatformResolver, $baseUri, $access_token;
        
    public function __construct(PaymentPlatformResolver $paymentPlatformResolver)
    {
        $this->baseUri          = env('DIRECTUS_URL');
        $this->access_token     = env('DIRECTUS_TOKEN');
        $this->paymentPlatformResolver = $paymentPlatformResolver; 
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
        return "Bearer {$this->access_token}";
    }

    public function show()
    {
        $paymentPlatforms = [
            [
                "name" => "PayPal",
                "image" => "img/payment-platforms/paypal.jpg",
            ],
            [
                "name" => "Stripe",
                "image" => "img/payment-platforms/stripe.jpg",
            ]
        ];

        $plans = [
            [
                "slug" => "monthly",
                "price" => 1200,
                "visual_price" => "$12.00",
                "duration_in_days" => 30,
            ],
            [
                "slug" => "yearly",
                "price" => 9999,
                "visual_price" => "$99.99",
                "duration_in_days" => 365,
            ]
        ];

        return view('subscribe',[
            'plans' => $plans,
            'paymentPlatforms' => $paymentPlatforms
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'first_name'        => ['required'],
            'last_name'         => ['required'],
            'email'             => ['required', 'email'],
            'plan'              => ['required', 'exists:plans,slug'],
            'payment_platform'  => ['required'],
        ];

        $request->validate($rules);

        $user = $this->makeRequest(
            'POST',
            '/items/users',
            [],
            [
                'name'      => $request->first_name,
                'surname'   => $request->last_name,
                'email'     => $request->email,
            ],
            [],
            $isJsonRequest = true
        );

        session()->put('user', json_encode($user->data));

        $paymentPlatform = $this->paymentPlatformResolver->resolveService($request->payment_platform);

        session()->put('subscriptionPlatformId',$request->payment_platform);

        return $paymentPlatform->handleSubscription($request);

    }

    public function approval(Request $request)
    {
        $rules = [
            'plan'              => ['required', 'exists:plans,slug']
        ];

        $request->validate($rules);

        if(session()->has('subscriptionPlatformId'))
        {
            $paymentPlatform = $this->paymentPlatformResolver->resolveService(session()->get('subscriptionPlatformId'));

            if($paymentPlatform->validateSubscription($request))
            {
                $user = json_decode(session()->get('user'));
        
                $subscription = $this->makeRequest(
                    'POST',
                    '/items/subscriptions',
                    [],
                    [
                        'user_id'       => $user->id,
                        'newsletter_id' => 1, // @TODO: make it dynamic
                        'email'         => $request->plan,
                    ],
                    [],
                    $isJsonRequest = true
                );
        
                return redirect()
                    ->route('subscribe.show')
                    ->withSuccess(['payment' => "Thanks {$user->name}. You have now a " . ucfirst($request->plan) . " subscription. Start using it now"]);
            }
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('We cannot verify your Subscription, Try again please');
        
    }

    public function cancelled()
    {
        return redirect()
            ->route('subscribe.show')
            ->withErrors('You Cancelled, try again whenever you are ready');
    }
}
