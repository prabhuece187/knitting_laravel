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

        if($outward['searchInput'] === "")
        {
            $count = $outward['limit'];
            $page  = $outward['curpage'];

            $sorting = "desc";

            $data = Outward::with('customer')->with('mill');

            $total = $data->count();

            $data = $data->take($count)
                    ->skip($count*($page-1))
                    ->orderby('outwards.id','desc')
                    ->get();
        }
        else
        {
            $count = $outward['limit'];
            $page  = $outward['curpage'];

            $sorting = "desc";

    $datas = Outward::with(['customer', 'mill','inward'])->where(function ($query) use ($outward) {
                    $search = '%' . $outward['searchInput'] . '%';

            $query->where('outwards.customer_id', 'LIKE', $search)
                      ->orWhereHas('customer', function ($q) use ($search) {
                         $q->where('customer_name', 'LIKE', $search);
                      })
                  ->orWhere('outwards.mill_id', 'LIKE', $search)
                      ->orWhereHas('mill', function ($q) use ($search) {
                         $q->where('mill_name', 'LIKE', $search);
                      })
                  ->orWhere('outwards.inward_id', 'LIKE', $search)
                      ->orWhereHas('inward', function ($q) use ($search) {
                         $q->where('inward_no', 'LIKE', $search);
                      })
            ->orWhere('outwards.id', 'LIKE', $search)
            ->orWhere('outwards.outward_no', 'LIKE', $search)
            ->orWhere('outwards.outward_invoice_no', 'LIKE', $search)
            ->orWhere('outwards.outward_tin_no', 'LIKE', $search)
            ->orWhere('outwards.outward_date', 'LIKE', $search)
            ->orWhere('outwards.total_weight', 'LIKE', $search)
            ->orWhere('outwards.total_quantity', 'LIKE', $search)
            ->orWhere('outwards.outward_vehicle_no', 'LIKE', $search)
            ->orWhere('outwards.status', 'LIKE', $search);
        });


            $total = $datas->count();

            $data = $datas->take($count)
                    ->skip($count*($page-1))
                    ->orderby('outwards.id','desc')
                    ->get();
        }

        return response(['data' => $data , 'total' => $total]);
    }

    public function OutwardCreate(Request $request)
    {
        $data = Outward::select('outward_invoice_no')->orderBy('outward_invoice_no','DESC')->first();
        return isset($data)?($data->outward_invoice_no+1):1;
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $data = Outward::create($input);

        $details = $request->details;

        foreach ($details as $detail)
        {
            $detail['outward_id'] = $data->id;
            $detail['outward_no'] = $data['outward_no'];
            $detail['user_id'] = $input['user_id'];
            $detail['outward_detail_date'] = $data->outward_date;

            OutwardDetail::create($detail);
        }
        return response($data);
    }

    public function OutwardEdit($id)
    {
        $outward = $data = Outward::with('customer')->with('mill')->find($id);

        $outward['Items'] = OutwardDetail::with('item')->with('yarn_type')->where('outward_id',$id)->get();
        return response($outward);
    }

    public function OutwardUpdate(Request $request,$id)
    {
        $input = $request->all();
        $bill = Outward::find($id);

        $bill->update($input);
        $action = Outward::where('id',$id)->first();

        OutwardDetail::where('outward_id',$id)->delete();

        $details = $request->Items;
        foreach ($details as $detail)
        {
            $detail['outward_id'] = $action->id;
            $detail['outward_detail_date'] = $action->outward_date;
            $detail['user_id'] = $input['user_id'];
            OutwardDetail::create($detail);
        }
        return response($bill);
    }
}
