<?php

namespace App\Console\Commands;

use App\Models\Courier;
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


        $response = $this->getData('get.employees');

        if ($response->successful()) {
            $result = $response->json()['result']['data'];

            $map = [];
            foreach ($result as $employee) {
                if (!isset($employee['extension']) || !isset($employee['extension']['extension_phone_number'])) {
                    continue;
                }


                $map[$employee['id']] = $employee['extension']['extension_phone_number'];
            }

            $this->info('Data loaded successfully.');
        } else {
            throw new Exception("API Error");
        }

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


                $ext = $map[$sipLine['employee_id']] ?? null;
                if (!$ext) continue;

                $courier = Courier::where('internal_phone', $ext)->first();
                if ($courier) {
                    $courier->sipLine()->associate($line);
                    $courier->save();
                }
            }

            $this->info('Data populated successfully.');
        } else {
            $this->error('Failed to fetch data from the API.');
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
