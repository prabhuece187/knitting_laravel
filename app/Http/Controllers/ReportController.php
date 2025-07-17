<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Inward;
use App\Models\Outward;

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
                    ->orWhere('inward_id', 'like', "%{$searchData}%")
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

}
