<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Models\RemonlineOrderStatus;
use App\Models\RemonlineOrderType;
use App\Models\ReportPreset;
use App\Models\Status;
use App\Models\StatusChange;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{

    const NA_PROZVON_STATUS = 1474412;

    public function show()
    {
        $orderStatuses = RemonlineOrderStatus::all(['id', 'name'])->toArray();
        $orderTypes = RemonlineOrderType::all(['id', 'name'])->toArray();
        $presets = ReportPreset::all(['id', 'name', 'settings'])->toArray();
//        foreach ($presets as $key => $preset) {
//            $presets[$key]['settings'] = json_decode($preset['settings'], true);
//        }
        return Inertia::render('Reports/Show', [
            'orderStatuses' => $orderStatuses,
            'orderTypes' => $orderTypes,
            'presets' => $presets,
        ]);
    }

    public function fetchOrders(Request $request)
    {
        $rem = new RemonlineApi();
        $orderSelection = $request->query('orderSelection');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $types = $request->query('types');
        $statuses = $request->query('statuses');
        $start = (new DateTime($startDate, new DateTimeZone('Europe/Moscow')))->getTimestamp() * 1000;
        $end = (new DateTime($endDate, new DateTimeZone('Europe/Moscow')))->modify('+1 day')->getTimestamp() * 1000;

        $query = [
            'page' => 1,
            'types' => $types,
            'statuses' => $statuses
        ];

        if ($orderSelection === 'closed') {
            $query['closed_at'] = [$start, $end];
        } elseif ($orderSelection === 'created') {
            $query['created_at'] = [$start, $end];
        } elseif ($orderSelection === 'transit' || $orderSelection === 'prozvon') {
            unset($query['statuses']);
            $startDate = Carbon::parse($startDate . " 00:00:00");
            $endDate = Carbon::parse($endDate . " 23:59:59");

            if ($orderSelection === 'transit') {
                $transitStatuses = Status::where('transit', true)->pluck('status_id')->toArray();

                $transitOrderIds = StatusChange::query()
                    ->join('statuses', 'status_changes.new_status_id', '=', 'statuses.status_id')
                    ->where('statuses.transit', true)
                    ->whereBetween('status_changes.created_at', [$startDate, $endDate])
                    ->distinct()
                    ->pluck('status_changes.order_id')
                    ->toArray();
            } else {
                $transitStatuses = [self::NA_PROZVON_STATUS];

                $transitOrderIds = StatusChange::query()
                    ->where('new_status_id', self::NA_PROZVON_STATUS)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->distinct()
                    ->pluck('order_id')
                    ->toArray();
            }

            $query['ids'] = $transitOrderIds;

            $orderStatusChanges = StatusChange::query()
                ->whereIn('order_id', $transitOrderIds)
                ->orderBy('created_at', 'asc') // Optional: order changes chronologically.
                ->get()
                ->groupBy('order_id')
                ->map(function ($changes) {
                    return $changes->toArray(); // Convert each group's collection to an array.
                })
                ->toArray();
        }


        $acceptedStatuses = Status::where('accepted_by_operator', true)->pluck('status_id')->toArray();
        $successfulStatuses = Status::where('success_for_operator', true)->pluck('status_id')->toArray();

        $orders = array();
        do {
            $response = $rem->getOrders($query);
            $orders = array_merge($orders, $response['data']);
            $query['page']++;
        } while (count($orders) < $response['count']);

        $out = [];
        foreach ($orders as $order) {
            if (isset($order['closed_at'])) {

                $closedDate = (new DateTime('now', new DateTimeZone("Europe/Moscow")))
                    ->setTimestamp($order['closed_at'] / 1000)
                    ->format('d.m.Y');
            } else {
                $closedDate = null;
            }

            $createdDate = (new DateTime('now', new DateTimeZone("Europe/Moscow")))
                ->setTimestamp($order['created_at'] / 1000)
                ->format('d.m.Y');

            $revenue = $order['price'];

            $costParts = 0;
            foreach ($order['parts'] as $part) {
                $costParts += $part['cost'] * $part['amount'];
//        if ($part['cost'] > 1) {
//        }
            }

            $costOps = 0;
            foreach ($order['operations'] as $operation) {
                $costOps += $operation['cost'] * $operation['amount'];
            }

            // себестоимость - только части
            // стоимость услуг - только услуги
            // валовая = выр - только себестоимость (частей)
            // прибыль = выр - стоимость всего

            $statusId = $order['status']['id'];

            $orderId = $order['id'];
            if ($orderSelection === 'transit') {
                $accepted = 1;

                $success = 0;
                $transitFlag = false;
                foreach ($orderStatusChanges[$orderId] as $change) {
                    $changeStatusId = $change['new_status_id'];

                    if (!$transitFlag && in_array($changeStatusId, $transitStatuses)) {
                        $transitFlag = true;
                    } elseif ($transitFlag && in_array($changeStatusId, $successfulStatuses)) {
                        $success = 1;
                    }
                }

            } else {
                $accepted = (int)in_array($statusId, $acceptedStatuses);
                $success = (int)in_array($statusId, $successfulStatuses);
            }

            $item = [
                'id' => $orderId,
                'label' => $order['id_label'],
                'status' => $order['status']['name'] ?? null,
                'closed_date' => $closedDate,
                'created_date' => $createdDate,
                'device_type' => $order["custom_fields"]["f1070009"] ?? '',
                'city' => $order["custom_fields"]["f5192512"] ?? '',
                'diagonal' => $order["custom_fields"]["f1536267"] ?? '',
                'brand' => $order["custom_fields"]["f1070012"] ?? '',
                'master' => $order["custom_fields"]["f5166933"] ?? '',
                'operator' => $order["custom_fields"]["f2129012"] ?? '',
                'accepted_by_operator' => $accepted,
                'success_for_operator' => $success,
                'soglas' => $order["custom_fields"]["f3471787"] ?? '',
                'site' => $order["custom_fields"]["f4196099"] ?? '',
                'revenue' => $revenue,
                'costParts' => round($costParts, 2),
                'costOps' => round($costOps, 2),
//        'grossProfit' => round($revenue - $costParts, 2),
                'netProfit' => round($revenue - $costParts - $costOps, 2)
            ];

            $out[] = $item;
        }


        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


}
