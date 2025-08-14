<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Inward;
use App\Models\InwardDetail;
use App\Models\OutwardDetail;
use App\Models\Outward;
use App\Models\Mill;
use App\Models\YarnType;

class ReportController extends Controller
{
    //
    public function OverAllReport(Request $request)
    {
        // Get filter values from request
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $customerId = $request->input('customer_id');
        $millId = $request->input('mill_id');
        $searchData = $request->input('search_data');

        // Base query for Inwards
        $inwardQuery = Inward::with('customer', 'mill');

        // Apply filters for Inward
        if ($fromDate) {
            $inwardQuery->whereDate('inward_date', '>=', $fromDate);
        }
        if ($toDate) {
            $inwardQuery->whereDate('inward_date', '<=', $toDate);
        }
        if ($customerId) {
            $inwardQuery->where('customer_id', $customerId);
        }
        if ($millId) {
            $inwardQuery->where('mill_id', $millId);
        }
        if ($searchData) {
            $inwardQuery->where(function ($query) use ($searchData) {
                $query->where('inward_invoice_no', 'like', "%{$searchData}%")
                    ->orWhere('inward_tin_no', 'like', "%{$searchData}%")
                    ->orWhere('inward_no', 'like', "%{$searchData}%")
                    ->orWhere('id', 'like', "%{$searchData}%")
                    ->orWhere('inward_vehicle_no', 'like', "%{$searchData}%");
            });
        }

        $inwards = $inwardQuery->get();

        // Totals for Inward
        $inwardTotals = [
            'total_weight' => $inwards->sum('total_weight'),
            'total_quantity' => $inwards->sum('total_quantity'),
        ];

        // Base query for Outwards
        $outwardQuery = Outward::with('customer', 'mill');

        // Apply filters for Outward (same as Inward)
        if ($fromDate) {
            $outwardQuery->whereDate('outward_date', '>=', $fromDate);
        }
        if ($toDate) {
            $outwardQuery->whereDate('outward_date', '<=', $toDate);
        }
        if ($customerId) {
            $outwardQuery->where('customer_id', $customerId);
        }
        if ($millId) {
            $outwardQuery->where('mill_id', $millId);
        }
        if ($searchData) {
            $outwardQuery->where(function ($query) use ($searchData) {
                $query->where('outward_invoice_no', 'like', "%{$searchData}%")
                    ->orWhere('outward_tin_no', 'like', "%{$searchData}%")
                    ->orWhere('inward_id', 'like', "%{$searchData}%")
                    ->orWhere('outward_vehicle_no', 'like', "%{$searchData}%");
            });
        }

        $outwards = $outwardQuery->get();

        // Totals for Outward
        $outwardTotals = [
            'total_weight' => $outwards->sum('total_weight'),
            'total_quantity' => $outwards->sum('total_quantity'),
        ];

        // Calculate Balance
        $balance = [
            'balance_weight' => $inwardTotals['total_weight'] - $outwardTotals['total_weight'],
            'balance_quantity' => $inwardTotals['total_quantity'] - $outwardTotals['total_quantity'],
        ];

        // Return JSON response
        return response()->json([
            'inwards' => $inwards,
            'outwards' => $outwards,
            'totals' => [
                'inward' => $inwardTotals,
                'outward' => $outwardTotals,
                'balance' => $balance,
            ],
        ]);
    }

    public function OverAllDetailReport(Request $request)
    {
        // Get filter values from request
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $customerId = $request->input('customer_id');
        $millId = $request->input('mill_id');
        $searchData = $request->input('search_data');
        $inwardNo = $request->input('inward_no');
        $inwardId = $request->input('inward_id');

        // Base query for Inward with related data
        $inwardQuery = Inward::with([
            'customer',
            'mill',
            'inward_details',
            'outwards.outward_details.item',
            'outwards.outward_details.yarn_type',
            'outwards.customer',        // optional: if you need customer details on outward
            'outwards.mill'             // optional: if you need mill details on outward
        ]);

        // Apply filters for Inward
        if ($fromDate) {
            $inwardQuery->whereDate('inward_date', '>=', $fromDate);
        }
        if ($toDate) {
            $inwardQuery->whereDate('inward_date', '<=', $toDate);
        }
        if ($customerId) {
            $inwardQuery->where('customer_id', $customerId);
        }
        if ($millId) {
            $inwardQuery->where('mill_id', $millId);
        }
        if ($inwardNo) {
            $inwardQuery->where('inward_no', $inwardNo);
        }
        if ($inwardId) {
            $inwardQuery->where('id', $inwardId);
        }
        if ($searchData) {
            $inwardQuery->where(function ($query) use ($searchData) {
                $query->where('inward_invoice_no', 'like', "%{$searchData}%")
                    ->orWhere('inward_tin_no', 'like', "%{$searchData}%")
                    ->orWhere('inward_no', 'like', "%{$searchData}%")
                    ->orWhere('id', 'like', "%{$searchData}%")
                    ->orWhere('inward_vehicle_no', 'like', "%{$searchData}%");
            });
        }

        $inwards = $inwardQuery->get();

        // Totals for Inward
        $inwardTotals = [
            'total_weight' => $inwards->sum('total_weight'),
            'total_quantity' => $inwards->sum('total_quantity'),
        ];

        // Totals for Outward (from all related outwards)
        $outwardTotals = [
            'total_weight' => $inwards->pluck('outwards')->flatten()->sum('total_weight'),
            'total_quantity' => $inwards->pluck('outwards')->flatten()->sum('total_quantity'),
        ];

        // Calculate Balance
        $balance = [
            'balance_weight' => $inwardTotals['total_weight'] - $outwardTotals['total_weight'],
            'balance_quantity' => $inwardTotals['total_quantity'] - $outwardTotals['total_quantity'],
        ];

        return response()->json([
            'inwards' => $inwards,
            'totals' => [
                'inward' => $inwardTotals,
                'outward' => $outwardTotals,
                'balance' => $balance,
            ],
        ]);
    }

    public function CustomerLedgerInOut(Request $request)
    {
        $customerId = $request->id;
        $from = $request->from;
        $to = $request->to;

        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $inwards = Inward::where('customer_id', $customerId)
            ->whereBetween('inward_date', [$from, $to])
            ->get();

        $outwards = Outward::where('customer_id', $customerId)
            ->whereBetween('outward_date', [$from, $to])
            ->get();

        $ledger = [];

        foreach ($inwards as $inward) {
            $ledger[] = [
                'date' => $inward->inward_date,
                'type' => 'Inward',
                'description' => 'Inward DC No: ' . $inward->inward_no,
                'qty' => $inward->total_quantity,
                'weight' => $inward->total_weight,
            ];
        }

        foreach ($outwards as $outward) {
            $ledger[] = [
                'date' => $outward->outward_date,
                'type' => 'Outward',
                'description' => 'Outward DC No: ' . $outward->outward_no,
                'qty' => $outward->total_quantity,
                'weight' => $outward->total_weight,
            ];
        }

        // Sort ledger by date
        usort($ledger, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // Calculate totals
        $totalInwardQty = 0;
        $totalInwardWeight = 0;
        $totalOutwardQty = 0;
        $totalOutwardWeight = 0;

        foreach ($ledger as $entry) {
            if ($entry['type'] === 'Inward') {
                $totalInwardQty += $entry['qty'];
                $totalInwardWeight += $entry['weight'];
            } elseif ($entry['type'] === 'Outward') {
                $totalOutwardQty += $entry['qty'];
                $totalOutwardWeight += $entry['weight'];
            }
        }

        // Append total row
        $ledger[] = [
            'type' => 'Total',
            'qty_inward' => $totalInwardQty,
            'weight_inward' => $totalInwardWeight,
            'qty_outward' => $totalOutwardQty,
            'weight_outward' => $totalOutwardWeight,
        ];

        // ✅ Append loss row
        $ledger[] = [
            'type' => 'Loss',
            'qty_loss' => $totalInwardQty - $totalOutwardQty,
            'weight_loss' => $totalInwardWeight - $totalOutwardWeight,
        ];

        return response()->json([
            'customer' => $customer,
            'ledger'   => $ledger
        ]);
    }

    public function CustomerLedgerInOutItemWise(Request $request)
    {
        $customerId = $request->id;
        $from = $request->from;
        $to = $request->to;

        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        // Fetch inward details with item
        $inwardDetails = InwardDetail::with('item')
            ->whereHas('inward', function ($query) use ($customerId, $from, $to) {
                $query->where('customer_id', $customerId)
                    ->whereBetween('inward_date', [$from, $to]);
            })
            ->get();

        // Fetch outward details with item
        $outwardDetails = OutwardDetail::with('item')
            ->whereHas('outward', function ($query) use ($customerId, $from, $to) {
                $query->where('customer_id', $customerId)
                    ->whereBetween('outward_date', [$from, $to]);
            })
            ->get();

        $itemWiseReport = [];

        // Process inwards
        foreach ($inwardDetails as $detail) {
            $itemName = $detail->item->item_name ?? 'Unknown Item';

            if (!isset($itemWiseReport[$itemName])) {
                $itemWiseReport[$itemName] = [
                    'item' => $itemName,
                    'inward_qty' => 0,
                    'inward_weight' => 0,
                    'outward_qty' => 0,
                    'outward_weight' => 0,
                ];
            }

            $itemWiseReport[$itemName]['inward_qty'] += $detail->inward_qty;
            $itemWiseReport[$itemName]['inward_weight'] += $detail->inward_weight;
        }

        // Process outwards
        foreach ($outwardDetails as $detail) {
            $itemName = $detail->item->item_name ?? 'Unknown Item';

            if (!isset($itemWiseReport[$itemName])) {
                $itemWiseReport[$itemName] = [
                    'item' => $itemName,
                    'inward_qty' => 0,
                    'inward_weight' => 0,
                    'outward_qty' => 0,
                    'outward_weight' => 0,
                ];
            }

            $itemWiseReport[$itemName]['outward_qty'] += $detail->outward_qty;
            $itemWiseReport[$itemName]['outward_weight'] += $detail->outward_weight;
        }

        // Calculate loss for each item
        foreach ($itemWiseReport as &$data) {
            $data['loss_qty'] = $data['inward_qty'] - $data['outward_qty'];
            $data['loss_weight'] = $data['inward_weight'] - $data['outward_weight'];
        }

        // Reset indexes
        $itemWiseReport = array_values($itemWiseReport);

        return response()->json([
            'customer' => $customer,
            'item_wise_report' => $itemWiseReport
        ]);
    }
    
    public function ItemStockReport(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $itemId = $request->id;

        // Get inward & outward transactions for the given item and date range
        $inward = \DB::table('inward_details')
            ->select(
                'inward_details.inward_detail_date as date',
                'inward_details.inward_qty as qty',
                'inward_details.inward_weight as weight',
                \DB::raw("'Inward' as type")
            )
            ->where('inward_details.item_id', $itemId)
            ->whereBetween('inward_details.inward_detail_date', [$from, $to]);

        $outward = \DB::table('outward_details')
            ->select(
                'outward_details.outward_detail_date as date',
                'outward_details.outward_qty as qty',
                'outward_details.outward_weight as weight',
                \DB::raw("'Outward' as type")
            )
            ->where('outward_details.item_id', $itemId)
            ->whereBetween('outward_details.outward_detail_date', [$from, $to]);

        // Merge inward & outward and order by date
        $transactions = $inward
            ->unionAll($outward)
            ->orderBy('date', 'asc')
            ->get();

        // Calculate closing stock row by row
        $closingQty = 0;
        $closingWeight = 0;

        $transactions = $transactions->map(function ($row) use (&$closingQty, &$closingWeight) {
            if ($row->type === 'Inward') {
                $closingQty += $row->qty;
                $closingWeight += $row->weight;
            } else {
                $closingQty -= $row->qty;
                $closingWeight -= $row->weight;
            }

            $row->closing_qty = $closingQty;
            $row->closing_weight = $closingWeight;

            return $row;
        });

        // Fetch the item details
        $item = \App\Models\Item::find($itemId);

        return response()->json([
            'item' => $item,
            'stock_report' => $transactions
        ]);
    }
    
    public function ItemStockReportCustomerWise(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $itemId = $request->id;

        // Inward transactions joined via inwards -> customers
        $inward = \DB::table('inward_details')
            ->join('inwards', 'inward_details.inward_id', '=', 'inwards.id')
            ->join('customers', 'inwards.customer_id', '=', 'customers.id')
            ->select(
                'inward_details.inward_detail_date as date',
                'inward_details.inward_qty as qty',
                'inward_details.inward_weight as weight',
                'inwards.inward_no',
                \DB::raw("NULL as outward_no"), // no outward dc no in inward rows
                'customers.id as customer_id',
                'customers.customer_name',
                \DB::raw("'Inward' as type")
            )
            ->where('inward_details.item_id', $itemId)
            ->whereBetween('inward_details.inward_detail_date', [$from, $to]);

        // Outward transactions joined via outwards -> customers
        $outward = \DB::table('outward_details')
            ->join('outwards', 'outward_details.outward_id', '=', 'outwards.id')
            ->join('customers', 'outwards.customer_id', '=', 'customers.id')
            ->select(
                'outward_details.outward_detail_date as date',
                'outward_details.outward_qty as qty',
                'outward_details.outward_weight as weight',
                \DB::raw("NULL as inward_no"), // no inward dc no in outward rows
                'outwards.outward_no',
                'customers.id as customer_id',
                'customers.customer_name',
                \DB::raw("'Outward' as type")
            )
            ->where('outward_details.item_id', $itemId)
            ->whereBetween('outward_details.outward_detail_date', [$from, $to]);

        // Merge and order all transactions by date
        $allTransactions = $inward->unionAll($outward)
            ->orderBy('date', 'asc')
            ->get();

        // Group transactions by customer
        $grouped = $allTransactions->groupBy('customer_id');

        // For each customer, calculate running closing qty and weight
        $customerReports = [];

        foreach ($grouped as $customerId => $transactions) {
            $closingQty = 0;
            $closingWeight = 0;

            $transactions = $transactions->map(function ($row) use (&$closingQty, &$closingWeight) {
                if ($row->type === 'Inward') {
                    $closingQty += $row->qty;
                    $closingWeight += $row->weight;
                } else {
                    $closingQty -= $row->qty;
                    $closingWeight -= $row->weight;
                }

                $row->closing_qty = $closingQty;
                $row->closing_weight = $closingWeight;

                return $row;
            });

            $first = $transactions->first();

            $customerReports[] = [
                'customer_id' => $customerId,
                'customer_name' => $first->customer_name,
                'transactions' => $transactions,
                'closing_qty' => $closingQty,
                'closing_weight' => $closingWeight,
            ];
        }

        // Fetch item details
        $item = \App\Models\Item::find($itemId);

        return response()->json([
            'item' => $item,
            'customerReports' => $customerReports,
        ]);
    }

    public function MillLedgerInOut(Request $request)
    {
        $millId = $request->id;
        $from = $request->from;
        $to = $request->to;

        $mill = Mill::find($millId);

        if (!$mill) {
            return response()->json(['message' => 'Mill not found'], 404);
        }

        // Fetch inward and outward transactions for mill in date range
        $inwards = Inward::where('mill_id', $millId)
            ->whereBetween('inward_date', [$from, $to])
            ->get();

        $outwards = Outward::where('mill_id', $millId)
            ->whereBetween('outward_date', [$from, $to])
            ->get();

        $ledger = [];

        foreach ($inwards as $inward) {
            $ledger[] = [
                'date' => $inward->inward_date,
                'type' => 'Inward',
                'description' => 'Inward DC No: ' . $inward->inward_no,
                'qty' => $inward->total_quantity,
                'weight' => $inward->total_weight,
            ];
        }

        foreach ($outwards as $outward) {
            $ledger[] = [
                'date' => $outward->outward_date,
                'type' => 'Outward',
                'description' => 'Outward DC No: ' . $outward->outward_no,
                'qty' => $outward->total_quantity,
                'weight' => $outward->total_weight,
            ];
        }

        // Sort ledger by date
        usort($ledger, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // Calculate totals
        $totalInwardQty = 0;
        $totalInwardWeight = 0;
        $totalOutwardQty = 0;
        $totalOutwardWeight = 0;

        foreach ($ledger as $entry) {
            if ($entry['type'] === 'Inward') {
                $totalInwardQty += $entry['qty'];
                $totalInwardWeight += $entry['weight'];
            } elseif ($entry['type'] === 'Outward') {
                $totalOutwardQty += $entry['qty'];
                $totalOutwardWeight += $entry['weight'];
            }
        }

        // Append total row
        $ledger[] = [
            'type' => 'Total',
            'qty_inward' => $totalInwardQty,
            'weight_inward' => $totalInwardWeight,
            'qty_outward' => $totalOutwardQty,
            'weight_outward' => $totalOutwardWeight,
        ];

        // Append loss row
        $ledger[] = [
            'type' => 'Loss',
            'qty_loss' => $totalInwardQty - $totalOutwardQty,
            'weight_loss' => $totalInwardWeight - $totalOutwardWeight,
        ];

        return response()->json([
            'mill' => $mill,
            'ledger' => $ledger,
        ]);
    }

    public function MillLedgerInOutItemWise(Request $request)
    {
        $millId = $request->id;
        $from = $request->from;
        $to = $request->to;

        $mill = Mill::find($millId);

        if (!$mill) {
            return response()->json(['message' => 'Mill not found'], 404);
        }

        $reportData = [];

        // Inward
        $inwardDetails = InwardDetail::with('item', 'inward')
            ->whereHas('inward', function ($query) use ($millId, $from, $to) {
                $query->where('mill_id', $millId)
                    ->whereBetween('inward_date', [$from, $to]);
            })
            ->get();

        foreach ($inwardDetails as $detail) {
            $reportData[] = [
                'date' => $detail->inward->inward_date ?? null,
                'type' => 'Inward',
                'dc_no' => $detail->inward->inward_no ?? '-',
                'item' => $detail->item->item_name ?? 'Unknown Item',
                'qty' => $detail->inward_qty,
                'weight' => $detail->inward_weight
            ];
        }

        // Outward
        $outwardDetails = OutwardDetail::with('item', 'outward')
            ->whereHas('outward', function ($query) use ($millId, $from, $to) {
                $query->where('mill_id', $millId)
                    ->whereBetween('outward_date', [$from, $to]);
            })
            ->get();

        foreach ($outwardDetails as $detail) {
            $reportData[] = [
                'date' => $detail->outward->outward_date ?? null,
                'type' => 'Outward',
                'dc_no' => $detail->outward->outward_no ?? '-',
                'item' => $detail->item->item_name ?? 'Unknown Item',
                'qty' => $detail->outward_qty,
                'weight' => $detail->outward_weight
            ];
        }

        // Sort by date (oldest first)
        usort($reportData, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // Running totals
        $closingQty = 0;
        $closingWeight = 0;

        foreach ($reportData as &$row) {
            if ($row['type'] === 'Inward') {
                $closingQty += $row['qty'];
                $closingWeight += $row['weight'];
            } else { // Outward
                $closingQty -= $row['qty'];
                $closingWeight -= $row['weight'];
            }
            $row['closing_qty'] = $closingQty;
            $row['closing_weight'] = $closingWeight;
        }

        return response()->json([
            'mill' => $mill,
            'report' => $reportData
        ]);
    }
    
    public function YarnTypeLedger(Request $request)
    {
        $yarnTypeId = $request->yarn_type_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Fetch yarn type info
        $yarnType = YarnType::find($yarnTypeId);

        // Inward details
        $inwardDetails = InwardDetail::with('inward')
            ->where('yarn_type_id', $yarnTypeId)
            ->whereHas('inward', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('inward_date', [$fromDate, $toDate]);
            })
            ->get();

        // Outward details
        $outwardDetails = OutwardDetail::with('outward')
            ->where('yarn_type_id', $yarnTypeId)
            ->whereHas('outward', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('outward_date', [$fromDate, $toDate]);
            })
            ->get();

        $rows = [];

        // Merge inward
        foreach ($inwardDetails as $detail) {
            $rows[] = [
                'date' => $detail->inward->inward_date,
                'type' => 'Inward',
                'inward_no' => $detail->inward->inward_no,
                'outward_no' => null,
                'in_qty' => $detail->inward_qty,
                'out_qty' => 0,
                'in_weight' => $detail->inward_weight,
                'out_weight' => 0
            ];
        }

        // Merge outward
        foreach ($outwardDetails as $detail) {
            $rows[] = [
                'date' => $detail->outward->outward_date,
                'type' => 'Outward',
                'inward_no' => null,
                'outward_no' => $detail->outward->outward_no,
                'in_qty' => 0,
                'out_qty' => $detail->outward_qty,
                'in_weight' => 0,
                'out_weight' => $detail->outward_weight
            ];
        }

        // Sort by date
        usort($rows, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // Calculate closing balances
        $closingQty = 0;
        $closingWeight = 0;

        foreach ($rows as &$row) {
            $closingQty += $row['in_qty'] - $row['out_qty'];
            $closingWeight += $row['in_weight'] - $row['out_weight'];
            $row['closing_qty'] = $closingQty;
            $row['closing_weight'] = $closingWeight;
        }

        return response()->json([
            'yarn_type' => $yarnType,
            'ledger' => $rows,
        ]);
    }


}
