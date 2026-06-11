<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Inward;
use App\Models\JobMaster;
use App\Models\Outward;
use App\Models\Payment;
use App\Models\Invoice;
use Auth;
use DB;

class DashboardController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        /* ===============================
           SUMMARY CARDS
        =============================== */

        $totalCustomers = Customer::where('user_id', $userId)->count();

        $todayInwardWeight = Inward::where('user_id', $userId)
            ->whereDate('inward_date', today())
            ->sum('total_weight');

        $activeJobs = JobMaster::where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        $todayDispatch = Outward::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->sum('total_weight');

        $todayInvoice = Invoice::where('user_id', $userId)
            ->whereDate('invoice_date', today())
            ->sum('invoice_total');

        $todayCollection = Payment::where('user_id', $userId)
            ->whereDate('payment_date', today())
            ->sum('amount');

        // $pendingAmount = Invoice::where('user_id', $userId)
        //     ->where('status', 'pending')
        //     ->sum('balance_amount');

        $totalInvoiceAmount = Invoice::where('user_id', $userId)
            ->sum('invoice_total');

        $totalPaidAmount = Payment::where('user_id', $userId)
            ->sum('amount');

        $pendingAmount = max(0, $totalInvoiceAmount - $totalPaidAmount);



        /* ===============================
           PRODUCTION CHART (LAST 7 DAYS)
        =============================== */

        $productionChart = DB::table('inward_details')
        ->join('inwards', 'inwards.id', '=', 'inward_details.inward_id')
        ->selectRaw('DATE(inwards.inward_date) as date, SUM(inward_details.inward_weight) as total')
        ->where('inwards.user_id', $userId)
        ->whereBetween('inwards.inward_date', [now()->subDays(300), now()])
        ->groupByRaw('DATE(inwards.inward_date)')
        ->orderBy('date')
        ->get();

        /* ===============================
           TOP CUSTOMERS
        =============================== */

        $topCustomers = Inward::select(
                'customers.customer_name',
                DB::raw('SUM(inwards.total_weight) as total_weight')
            )
            ->join('customers', 'customers.id', '=', 'inwards.customer_id')
            ->where('inwards.user_id', $userId)
            ->groupBy('customers.customer_name')
            ->orderByDesc('total_weight')
            ->limit(5)
            ->get();



        /* ===============================
           RECENT ACTIVITIES
        =============================== */

        $recentInwards = Inward::select('id','inward_no','created_at')
            ->where('user_id', $userId)
            ->latest()
            ->limit(3)
            ->get();

        $recentJobs = JobMaster::select('id','job_card_no','created_at')
            ->where('user_id', $userId)
            ->latest()
            ->limit(3)
            ->get();



        return response()->json([

            "summary" => [
                "total_customers" => $totalCustomers,
                "today_inward_weight" => $todayInwardWeight,
                "active_jobs" => $activeJobs,
                "today_dispatch" => $todayDispatch,
                "today_invoice" => $todayInvoice,
                "today_collection" => $todayCollection,
                "pending_amount" => $pendingAmount
            ],

            "production_chart" => $productionChart,

            "top_customers" => $topCustomers,

            "recent_inwards" => $recentInwards,

            "recent_jobs" => $recentJobs

        ]);

    }

}