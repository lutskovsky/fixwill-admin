<?php

namespace App\Console\Commands;

use App\Integrations\RemonlineApi;
use App\Models\Chat;
use Illuminate\Console\Command;

class SynchClientIdsInChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:synch-client-ids-in-chats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(RemonlineApi $rem)
    {

        foreach (Chat::all() as $chat) {
            if ($chat->client_id) {
                continue;
            }
            $this->info($chat->id);
            $response = $rem->getClients(['phones' => [$chat->visitor_phone]]);
            $clients = $response['data'];
            if (!$clients) {
                continue;
            }
            $clientId = $clients[0]['id'];

            $chat->client_id = $clientId;
            $chat->save();
        }
    }
}
