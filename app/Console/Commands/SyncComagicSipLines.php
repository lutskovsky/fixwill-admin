<?php

namespace App\Console\Commands;

use App\Models\SipLine;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SyncComagicSipLines extends Command
{
    protected $signature = 'comagic:sync-sip-lines';
    protected $description = 'Sync sip_lines table with data from the API';

    public function handle()
    {
        $response = $this->getData('get.sip_lines');

        if ($response->successful()) {
            $result = $response->json()['result']['data'];

            foreach ($result as $sipLine) {
                $line = SipLine::updateOrCreate(
                    ['employee_id' => $sipLine['employee_id'],],
                    [
                        'employee_name' => $sipLine['employee_full_name'],
                        'phone_number' => $sipLine['phone_number'],
                        'virtual_number' => $sipLine['virtual_phone_number']
                    ]);
            }

            $this->info('Data populated successfully.');
        } else {
            throw new Exception('Failed to fetch data from the API.');
        }
    }

    /**
     * @param $method
     * @return PromiseInterface|Response
     */
    protected function getData($method): Response|PromiseInterface
    {
        $url = 'https://dataapi.comagic.ru/v2.0';
        $data = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'id' => 'bot',
            'params' => [
                'access_token' => 'r6bf2wy0zpotcv6qfx93jcdbz0cxtyif447u6afz'
            ]
        ];

        $response = Http::post($url, $data);
        return $response;
    }
}
