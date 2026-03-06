<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Outward;
use App\Models\OutwardDetail;
use App\Models\YarnType;
use App\Models\Mill;
use App\Models\KnittingProductionDetail;
use App\Models\KnittingRework;

class OutwardController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = Outward::with(['customer', 'mill', 'inward'])
            ->select('outwards.*');

        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('outwards.id', 'like', "%{$search}%")
                ->orWhere('outwards.outward_no', 'like', "%{$search}%")
                ->orWhere('outwards.outward_invoice_no', 'like', "%{$search}%")
                ->orWhere('outwards.outward_date', 'like', "%{$search}%")
                ->orWhere('outwards.vehicle_no', 'like', "%{$search}%")
                ->orWhere('outwards.total_weight', 'like', "%{$search}%")
                ->orWhere('outwards.process_type', 'like', "%{$search}%")
                ->orWhere('outwards.expected_gsm', 'like', "%{$search}%")
                ->orWhere('outwards.expected_dia', 'like', "%{$search}%")
                ->orWhere('outwards.job_card_no', 'like', "%{$search}%")
                ->orWhere('outwards.remarks', 'like', "%{$search}%");

                $q->orWhereHas('customer', function ($c) use ($search) {
                    $c->where('customer_name', 'like', "%{$search}%");
                });

                $q->orWhereHas('mill', function ($m) use ($search) {
                    $m->where('mill_name', 'like', "%{$search}%");
                });

                $q->orWhereHas('inward', function ($i) use ($search) {
                    $i->where('inward_no', 'like', "%{$search}%");
                });
            });
        }

        $query->orderBy('outwards.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    public function OutwardCreate(Request $request)
    {
        $data = Outward::select('outward_invoice_no')
            ->orderBy('outward_invoice_no','DESC')
            ->first();

        return isset($data) ? ($data->outward_invoice_no + 1) : 1;
    }

    public function store(Request $request)
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
            )->sum('weight');

            $requestOutwardQty = collect($request->outward_details)->sum('weight');

            $available = ($produced + $reworked) - $outward;

            if ($requestOutwardQty > $available) {
                return response(['error'=>'Insufficient knitting fabric stock'],422);
            }
        }

        // SAVE HEADER
        $data = Outward::create($input);

        foreach ($request->outward_details as $detail)
        {
            $detail['outward_id'] = $data->id;
            $detail['outward_no'] = $data->outward_no;
            $detail['user_id'] = Auth::id(); // ✅ AUTH USER
            $detail['outward_detail_date'] = $data->outward_date;

            OutwardDetail::create($detail);
        }

        return response($data);
    }

    public function OutwardEdit($id)
    {
        $outward = Outward::with('customer','mill')->find($id);

        $outward['Items'] = OutwardDetail::with('item','yarnType')
            ->where('outward_id',$id)
            ->get();

        return response($outward);
    }

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
            $detail['user_id'] = Auth::id(); // ✅ AUTH USER

            OutwardDetail::create($detail);
        }

        return response($bill);
    }

}