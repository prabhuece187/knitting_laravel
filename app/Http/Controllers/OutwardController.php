<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Outward;
use App\Models\OutwardDetail;
use App\Models\YarnType;
use App\Models\Mill;


class OutwardController extends Controller
{
    // public function index(Request $request)
    // {
    //     $outward = $request->all();

    //     $count = $outward['limit'];
    //     $page  = $outward['curpage'];

    //     $sorting = "desc";

    //     $data = Outward::with('customer')->with('mill');

    //     $total = $data->count();

    //     $data = $data->take($count)
    //             ->skip($count*($page-1))
    //             ->orderby('outwards.id','desc')
    //             ->get();  

    //     return response(['data' => $data , 'total' => $total]);
    // }

    public function index(Request $request)
    {
        $outward = $request->all();

        $count = $outward['limit'];
        $page  = $outward['curpage'];
        $searchInput = trim($outward['searchInput'] ?? "");

        // Base query with relationships
        $query = Outward::with(['customer', 'mill', 'inward']);

        // --------------------------------------
        // 🔍 If search text entered → apply filter
        // --------------------------------------
        if ($searchInput !== "") {

            $search = "%" . $searchInput . "%";

            $query->where(function ($q) use ($search) {

                // Search outward table fields
                $q->where('outwards.id', 'LIKE', $search)
                ->orWhere('outwards.outward_no', 'LIKE', $search)
                ->orWhere('outwards.outward_invoice_no', 'LIKE', $search)
                ->orWhere('outwards.outward_date', 'LIKE', $search)
                ->orWhere('outwards.vehicle_no', 'LIKE', $search)
                ->orWhere('outwards.total_weight', 'LIKE', $search)
                ->orWhere('outwards.process_type', 'LIKE', $search)
                ->orWhere('outwards.expected_gsm', 'LIKE', $search)
                ->orWhere('outwards.expected_dia', 'LIKE', $search)
                ->orWhere('outwards.job_card_no', 'LIKE', $search)
                ->orWhere('outwards.remarks', 'LIKE', $search);

                // Search Customer
                $q->orWhereHas('customer', function ($c) use ($search) {
                    $c->where('customer_name', 'LIKE', $search);
                });

                // Search Mill
                $q->orWhereHas('mill', function ($m) use ($search) {
                    $m->where('mill_name', 'LIKE', $search);
                });

                // Search Inward
                $q->orWhereHas('inward', function ($i) use ($search) {
                    $i->where('inward_no', 'LIKE', $search);
                });
            });
        }

        // Total count before pagination
        $total = $query->count();

        // Apply pagination
        $data = $query->orderBy('outwards.id', 'desc')
                    ->skip($count * ($page - 1))
                    ->take($count)
                    ->get();

        return response([
            'data'  => $data,
            'total' => $total
        ]);
    }

    public function OutwardCreate(Request $request)
    {
        $data = Outward::select('outward_invoice_no')->orderBy('outward_invoice_no','DESC')->first();
        return isset($data)?($data->outward_invoice_no+1):1;
    }

    // public function store(Request $request)
    // {
    //     $input = $request->all();
    //     $data = Outward::create($input);

    //     $details = $request->outward_details;

    //     foreach ($details as $detail)
    //     {
    //         $detail['outward_id'] = $data->id;
    //         $detail['outward_no'] = $data['outward_no'];
    //         $detail['user_id'] = $input['user_id'];
    //         $detail['outward_detail_date'] = $data->outward_date;

    //         OutwardDetail::create($detail);
    //     }
    //     return response($data);
    // }

    public function store(Request $request)
    {
        $input = $request->all();

        // 🔒 KNITTING STOCK VALIDATION
        if ($input['process_type'] === 'knitting' && !empty($input['job_card_no'])) {

            $jobCardId = $input['job_card_no']; // assuming job_card_no = job_card_id

            $produced = KnittingProductionDetail::whereHas(
                'production',
                fn($q)=>$q->where('job_card_id',$jobCardId)
            )->sum('produced_weight');

            $reworked = KnittingRework::where('job_card_id',$jobCardId)
                            ->sum('rework_qty');

            $outward = OutwardDetail::whereHas(
                'outward',
                fn($q)=>$q->where('job_card_no',$jobCardId)
            )->sum('weight'); // use correct column name

            $requestOutwardQty = collect($request->outward_details)->sum('weight');

            $available = ($produced + $reworked) - $outward;

            if ($requestOutwardQty > $available) {
                return response(['error'=>'Insufficient knitting fabric stock'],422);
            }
        }

        // ✅ SAFE TO SAVE
        $data = Outward::create($input);

        foreach ($request->outward_details as $detail)
        {
            $detail['outward_id'] = $data->id;
            $detail['outward_no'] = $data->outward_no;
            $detail['user_id'] = $input['user_id'];
            $detail['outward_detail_date'] = $data->outward_date;

            OutwardDetail::create($detail);
        }

        return response($data);
    }


    public function OutwardEdit($id)
    {
        $outward = $data = Outward::with('customer')->with('mill')->find($id);

        $outward['Items'] = OutwardDetail::with('item')->with('yarnType')->where('outward_id',$id)->get();
        return response($outward);
    }

    // public function OutwardUpdate(Request $request,$id)
    // {
    //     $input = $request->all();
    //     $bill = Outward::find($id);

    //     $bill->update($input);
    //     $action = Outward::where('id',$id)->first();

    //     OutwardDetail::where('outward_id',$id)->delete();

    //     $details = $request->Items;
    //     foreach ($details as $detail)
    //     {
    //         $detail['outward_id'] = $action->id;
    //         $detail['outward_detail_date'] = $action->outward_date;
    //         $detail['user_id'] = $input['user_id'];
    //         OutwardDetail::create($detail);
    //     }
    //     return response($bill);
    // }

    public function OutwardUpdate(Request $request,$id)
    {
        $input = $request->all();

        // 🔒 KNITTING STOCK VALIDATION
        if ($input['process_type'] === 'knitting' && !empty($input['job_card_no'])) {

            $jobCardId = $input['job_card_no'];

            $produced = KnittingProductionDetail::whereHas(
                'production',
                fn($q)=>$q->where('job_card_id',$jobCardId)
            )->sum('produced_weight');

            $reworked = KnittingRework::where('job_card_id',$jobCardId)
                            ->sum('rework_qty');

            $outward = OutwardDetail::whereHas(
                'outward',
                fn($q)=>$q->where('job_card_no',$jobCardId)
            )
            ->where('outward_id','!=',$id)
            ->sum('weight');

            $requestOutwardQty = collect($request->Items)->sum('weight');

            $available = ($produced + $reworked) - $outward;

            if ($requestOutwardQty > $available) {
                return response(['error'=>'Insufficient knitting fabric stock'],422);
            }
        }

        // UPDATE HEADER
        $bill = Outward::find($id);
        $bill->update($input);

        // UPDATE DETAILS
        OutwardDetail::where('outward_id',$id)->delete();

        foreach ($request->Items as $detail)
        {
            $detail['outward_id'] = $bill->id;
            $detail['outward_detail_date'] = $bill->outward_date;
            $detail['user_id'] = $input['user_id'];

            OutwardDetail::create($detail);
        }

        return response($bill);
    }

}
