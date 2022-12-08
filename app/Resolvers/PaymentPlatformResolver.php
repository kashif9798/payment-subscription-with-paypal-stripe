<?php

namespace App\Resolvers;

class PaymentPlatformResolver
{
    protected $paymentPlatforms;

    public function resolveService($paymentPlatform)
    {
        $paymentPlatform = strtolower($paymentPlatform);
        $service = config("services.{$paymentPlatform}.class");

        if($service){
            return resolve($service);
        }

        throw new \Exception('The selected platform is not in the configuration');
    }
}