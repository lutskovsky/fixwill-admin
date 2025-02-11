<?php
// app/Http/Controllers/StatusChangeController.php
namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Models\StatusChange;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class StatusChangeController extends Controller
{
    public function store(Request $request)
    {
//        if ($request->context->object_type != 'order') {
//            return response('Not an order', 200);
//        }
        $validated = $request->validate([
            'context.object_id' => 'required|integer',
            'metadata.new.id' => 'required|integer',
            'metadata.old.id' => 'required|integer',
        ]);

        $orderId = $validated['context']['object_id'];
        $newStatusId = $validated['metadata']['new']['id'];
        StatusChange::create([
            'new_status_id' => $newStatusId,
            'old_status_id' => $validated['metadata']['old']['id'],
            'order_id' => $orderId,
        ]);

        // If not spam or duplicate
        if ($newStatusId != 433229 && $newStatusId != 353142) {

            $rem = new RemonlineApi();
            $order = $rem->getOrderById($orderId)['data'];

            // if no operator
            if (!isset($order["custom_fields"]["f2129012"]) || !$order["custom_fields"]["f2129012"]) {
                $token = config('telegramBots.notifications');
                $botService = new TelegramBotService($token);
                $msg = "Заказ <a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a> без оператора.";
                $botService->sendMessage('-1002373384758', $msg);
            }
        }

        return response()->json(['message' => 'Status change recorded successfully'], 200);
    }
}
