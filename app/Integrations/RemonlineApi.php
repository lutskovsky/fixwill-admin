<?php

namespace App\Integrations;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class RemonlineApi
{
    const REM_BOOLEAN_FIELD_TYPE = 0;
    const REM_BRANCH_ID = 0;
    public array $legalEntityFields = [
        1644356,
        1070023,
        1070023,
        1070026,
        1070027,
        1070029,
        1070030,
        1070031,
        1070032,
    ];
    public $employees = [];
    protected $token;
    protected $api_key;
    protected $api_url = 'https://api.remonline.app/';
    protected $client;

    public function __construct()
    {
        $this->api_key = env('REMONLINE_TOKEN');
        $this->client = new Client(['http_errors' => false]);
        $this->getNewToken();
    }

    public function getNewToken()
    {
        $response = $this->client->request('POST', $this->api_url . 'token/new', [
                'form_params' => [
                    'api_key' => $this->api_key
                ]
            ]
        );
        $result = json_decode($response->getBody(), true);
        $this->token = $result['token'];
        return $this->token;
    }

    public function getOrderTypes()
    {
        return $this->apiCall('order/types/');
    }

    private function apiCall($method, $data = [], $httpMethod = 'GET', $try = 1)
    {
        $data['token'] = $this->token;
        $url = urldecode($this->api_url . $method . '?' . $this->generateCorrectParams($data));
        //var_dump($url);
        try {
            $response = $this->client->request($httpMethod, $url);

            return json_decode($response->getBody(), true);
        } catch (RequestException|ClientException $e) {
            if ($try > 5) {
                $msg = $e->getMessage();
                echo "$msg\n";
                return false;
            }

            $data['token'] = $this->getNewToken();
            return $this->apiCall($method, $data, $httpMethod, $try + 1);

        }
    }

    protected function generateCorrectParams($data)
    {
        $str = '';
        foreach ($data as $param_name => $item) {
            if (is_array($item)) { //собираем строчку из массива

                if ($param_name === 'custom_fields') {
                    $str .= $param_name . '=' . json_encode($item) . '&';
                } else {
                    foreach ($item as $tmp) {
                        $str .= $param_name . '[]=' . $tmp . '&';
                    }
                }
            } else {
                $str .= $param_name . '=' . $item . '&';
            }
        }
        $str = mb_substr($str, 0, -1);
        return $str;
    }

    public function getOrderCustomFields()
    {
        return $this->apiCall('order/custom-fields/');
    }

    public function getClientCustomFields()
    {
        return $this->apiCall('clients/custom-fields/');
    }

    public function getStatuses()
    {
        return $this->apiCall('statuses/');
    }

    public function getBranches()
    {
        return $this->apiCall('branches/');
    }

    public function getEmployee($id, $lastNameOnly = false)
    {
        if ($this->employees == [] || empty($this->employees[$id])) {
            $employees = $this->apiCall('employees/');
            foreach ($employees['data'] as $employeeArr) {
                $this->employees[$employeeArr['id']] = $employeeArr;
            }
        }

        $return = '';

        if (!empty($this->employees[$id])) {
            if ($lastNameOnly) {
                $return = $this->employees[$id]['last_name'];
            } else {
                $return = $this->employees[$id]['first_name'] . ' ' . $this->employees[$id]['last_name'];
            }
        }

        return $return;

    }

    public function getOrders($data)
    {
        return $this->apiCall('order/', $data);
    }

    public function getClients($data = [])
    {
        return $this->apiCall('clients/', $data);
    }

    public function getOperations($data = [])
    {
        return $this->apiCall('books/service-operations/', $data);
    }

    public function getBooks($data = [])
    {
        return $this->apiCall('book/list/', $data);
    }

    public function getBookItems($id, $data = [])
    {
        return $this->apiCall('book-item/list/' . $id, $data);
    }

    public function getCashboxes($data = [])
    {
        return $this->apiCall('cashbox/', $data);
    }

    public function createOrder($data = [])
    {
        return $this->apiCall('order/', $data, 'POST');
    }

    public function createClient($data = [])
    {
        return $this->apiCall('clients/', $data, 'POST');
    }

    public function updateClient($data = [])
    {
        return $this->apiCall('clients/', $data, 'PUT');
    }

    public function getEmployees()
    {
        return $this->apiCall('employees/');
    }
}

