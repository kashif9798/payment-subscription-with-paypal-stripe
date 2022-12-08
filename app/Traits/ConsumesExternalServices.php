<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait ConsumesExternalServices
{
    public function makeRequest($method, $requestUrl, $queryParams = [], $formParams = [], $headers = [], $isJsonRequest = false)
    {
        $client = new Client([
            'base_uri'  => $this->baseUri,
        ]);

        if(method_exists($this,'resolveAuthorization')){
            $this->resolveAuthorization($queryParams, $headers, $formParams);
        }
        
        try{
            $response = $client->request($method, $requestUrl, [
                $isJsonRequest ? 'json' : 'form_parmas' => $formParams,
                'headers'                               => $headers,
                'query'                                 => $queryParams
            ]);
    
            $response = $response->getBody()->getContents();
        } catch(\Throwable $e){
            dd($method, $requestUrl, $queryParams, $formParams, $headers, $isJsonRequest);

        }
        

        if(method_exists($this,'decodeResponse')){
            $response = $this->decodeResponse($response);
        }

        return $response;

    }
}