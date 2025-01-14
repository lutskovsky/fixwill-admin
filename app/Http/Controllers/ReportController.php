<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Models\RemonlineOrderStatus;
use App\Models\RemonlineOrderType;
use App\Models\ReportPreset;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{

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

        $onlyClosed = ($orderSelection === 'closed');
        if ($onlyClosed) {
            $query['closed_at'] = [$start, $end];
        }

        $orders = array();
        do {
            $response = $rem->getOrders($query);
            $orders = array_merge($orders, $response['data']);
            $query['page']++;
        } while (count($orders) < $response['count']);

        $out = [];
        foreach ($orders as $order) {
            if (isset($order['closed_at'])) {

                $closeDate = (new DateTime('now', new DateTimeZone("Europe/Moscow")))
                    ->setTimestamp($order['closed_at'] / 1000)
                    ->format('d.m.Y');
            } else {
                $closeDate = null;
            }

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
            // прибыль = выр -  стоимость всего

            $item = [
//            'label' => "<a href='fsfff.io' target='_blank'>" . $order['id_label'] . "</a>",

                'id' => $order['id'],
                'label' => $order['id_label'],
                'status' => $order['status']['name'] ?? null,
                'date' => $closeDate,
                'device_type' => $order["custom_fields"]["f1070009"] ?? '',
                'city' => $order["custom_fields"]["f5192512"] ?? '',
                'diagonal' => $order["custom_fields"]["f1536267"] ?? '',
                'brand' => $order["custom_fields"]["f1070012"] ?? '',
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
