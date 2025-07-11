<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Inward;
use App\Models\InwardDetail;
use App\Models\YarnType;

class InwardController extends Controller
{
    public function index(Request $request)
    {
        $inward = $request->all();

        $count = $inward['limit'];
        $page  = $inward['curpage'];
        $search = $inward['searchInput'];
        $sorting = "desc";

        $query = Inward::with('customer', 'mill');

        if (!empty($search)) {
            $query = $query->where(function ($q) use ($search) {
                $q->where('inwards.customer_id', 'LIKE', "%$search%")
                ->orWhere('inwards.mill_id', 'LIKE', "%$search%")
                ->orWhere('inwards.id', 'LIKE', "%$search%")
                ->orWhere('inwards.inward_no', 'LIKE', "%$search%")
                ->orWhere('inwards.inward_invoice_no', 'LIKE', "%$search%")
                ->orWhere('inwards.inward_tin_no', 'LIKE', "%$search%")
                ->orWhere('inwards.inward_date', 'LIKE', "%$search%")
                ->orWhere('inwards.total_weight', 'LIKE', "%$search%")
                ->orWhere('inwards.total_quantity', 'LIKE', "%$search%")
                ->orWhere('inwards.inward_vehicle_no', 'LIKE', "%$search%")
                ->orWhere('inwards.status', 'LIKE', "%$search%");
            })
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%$search%");
            })
            ->orWhereHas('mill', function ($q) use ($search) {
                $q->where('mill_name', 'LIKE', "%$search%");
            });
        }

        $total = $query->count();

        $data = $query->orderBy('inwards.id', $sorting)
                    ->take($count)
                    ->skip($count * ($page - 1))
                    ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    public function InwardCreate(Request $request)
    {
        $data = Inward::select('inward_invoice_no')->orderBy('inward_invoice_no','DESC')->first();
        return isset($data)?($data->inward_invoice_no+1):1;
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $data = Inward::create($input);

        $details = $request->inward_details;

        foreach ($details as $detail)
        {
            $detail['inward_id'] = $data->id;
            $detail['user_id'] = $input['user_id'];
            $detail['inward_no'] = $data['inward_no'];
            $detail['Inward_Detail_Date'] = $data->inward_date;

            InwardDetail::create($detail);
        }
        return response($data);
    }

    public function InwardEdit($id)
    {
        $inward = $data = Inward::with('customer')->with('mill')->find($id);

        $inward['Items'] = InwardDetail::with('item')->with('yarn_type')->where('inward_id',$id)->get();
        return response($inward);
    }

    public function InwardUpdate(Request $request,$id)
    {
        $input = $request->all();
        $bill = Inward::find($id);

        $bill->update($input);
        $action = Inward::where('id',$id)->first();

        InwardDetail::where('inward_id',$id)->delete();

        // $details = $request->Items;

        $details = $request->inward_details;
        foreach ($details as $detail)
        {
            $detail['inward_id'] = $action->id;
            $detail['user_id'] = $input['user_id'];
            $detail['inward_detail_date'] = $action->inward_date;
            InwardDetail::create($detail);
        }
        return response($bill);
    }
}
