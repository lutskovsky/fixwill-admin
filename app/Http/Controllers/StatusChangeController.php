<?php
// app/Http/Controllers/StatusChangeController.php
namespace App\Http\Controllers;

use App\Events\StatusChanged;
use App\Integrations\RemonlineApi;
use App\Models\Order;
use App\Models\OrderType;
use App\Models\Status;
use App\Models\StatusChange;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class StatusChangeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'context.object_id' => 'required|integer',
            'metadata.new.id' => 'required|integer',
            'metadata.old.id' => 'required|integer',
        ]);

        $orderId = $validated['context']['object_id'];
        $newStatusId = $validated['metadata']['new']['id'];
        $statusChange = StatusChange::create([
            'new_status_id' => $newStatusId,
            'old_status_id' => $validated['metadata']['old']['id'],
            'order_id' => $orderId,
        ]);

        $rem = new RemonlineApi();
        $newData = $rem->getOrderById($orderId);
        $currentPickupDate = $newData['custom_fields']['f1482265'] ?? null;

        $savedOrder = Order::whereId($orderId)->first();

        if ($savedOrder) {
            $oldData = $savedOrder->data;
            $savedOrder->update(['data' => $newData]);
            if (!$savedOrder->initial_pickup_date) {
                $savedOrder->update(['initial_pickup_date' => $currentPickupDate]);
            }
        } else {
            $oldData = $newData;
            Order::create([
                'id' => $orderId,
                'label' => $newData['id_label'],
                'data' => $newData,
                'initial_pickup_date' => $currentPickupDate
            ]);
        }

        StatusChanged::dispatch($statusChange, $oldData, $newData);

        $statusCheck = Status::where(['status_id' => $newStatusId, 'operator_required' => true])->first();
        if (!$statusCheck) {
            return response('OK', 200);
        }

        $orderType = $newData['order_type']['id'];

        $typeCheck = OrderType::where(['type_id' => $orderType, 'operator_required' => true])->first();
        if (!$typeCheck) {
            return response('OK', 200);
        }

        if (!isset($newData["custom_fields"]["f2129012"]) || !$newData["custom_fields"]["f2129012"]) {
            $token = config('telegramBots.notifications');
            $botService = new TelegramBotService($token);
            $msg = "Заказ <a href='https://web.remonline.app/orders/table/$orderId'>{$newData['id_label']}</a> без оператора.";
            $botService->sendMessage('-1002373384758', $msg);
        }
        return response('OK', 200);
    }
}
