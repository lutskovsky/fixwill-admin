<?php

namespace App\Integrations;

use Exception;
use GuzzleHttp\Client;

class ComagicClient
{
    public array $latestFullResponse;
    private Client $HttpClient;
    private string $token;
    private string $url = 'https://callapi.comagic.ru/v4.0';

    public function __construct($token, $HttpClient)
    {
        $this->HttpClient = $HttpClient;
        $this->token = $token;
    }

    public function call($api, $method, $params = [])
    {
        $params['access_token'] = $this->token;
        if ($api == 'call') {
            $url = 'https://callapi.comagic.ru/v4.0';
        } elseif ($api == 'data') {
            $url = 'https://dataapi.comagic.ru/v2.0';
        } else {
            throw  new Exception('Unknown API type');
        }

        $request = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'id' => 'bot',
            'params' => $params
        ];

        $response = $this->HttpClient->request('POST', $url, ['json' => $request]);
        $responseStr = (string)$response->getBody();
        $response = json_decode($responseStr, true);

//        $this->latestFullResponse = $response;
//        if (isset($response['error'])) {
//            throw new \Exception($response['error']['message']);
//        }
        return $response; //['result']['data'];
    }

}
