<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Inward;
use App\Models\InwardDetail;
use App\Models\YarnType;
use Illuminate\Support\Facades\DB;

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

        $inward = Inward::create($input);

        foreach ($request->inward_details as $detail) {
            InwardDetail::create([
                'inward_id'   => $inward->id,
                'user_id'     => $input['user_id'],
                'item_id'     => $detail['item_id'],
                'yarn_type_id'=> $detail['yarn_type_id'],
                'shade'       => $detail['shade'] ?? null,
                'bag_no'      => $detail['bag_no'] ?? null,
                'gross_weight'=> $detail['gross_weight'] ?? 0,
                'tare_weight' => $detail['tare_weight'] ?? 0,
                'net_weight'  => $detail['net_weight'] ?? 0,
                'uom'         => $detail['uom'] ?? null,
                'remarks'     => $detail['remarks'] ?? null,
                'job_card_id' => null, // ✅ KEY LINE
            ]);
        }

        return response()->json($inward);
    }

    public function InwardEdit($id)
    {
        $inward = $data = Inward::with('customer')->with('mill')->find($id);

        $inward['Items'] = InwardDetail::with('item','jobMaster','yarnType')->where('inward_id',$id)->get();
        return response($inward);
    }

    // public function InwardUpdate(Request $request,$id)
    // {
    //     $input = $request->all();
    //     $bill = Inward::find($id);

    //     $bill->update($input);
    //     $action = Inward::where('id',$id)->first();

    //     InwardDetail::where('inward_id',$id)->delete();
    //     $details = $request->inward_details;
    //     foreach ($details as $detail)
    //     {
    //         $detail['inward_id'] = $action->id;
    //         $detail['user_id'] = $input['user_id'];
    //         $detail['inward_detail_date'] = $action->inward_date;
    //         InwardDetail::create($detail);
    //     }
    //     return response($bill);
    // }

    public function InwardUpdate(Request $request, $id)
    {
        $input = $request->all();

        $inward = Inward::findOrFail($id);
        $inward->update($input);

        $existingIds = $inward->inward_details()->pluck('id')->toArray();
        $incomingIds = collect($request->inward_details)
                        ->pluck('id')
                        ->filter()
                        ->toArray();

        // 🗑 Delete only removed rows
        $deleteIds = array_diff($existingIds, $incomingIds);
        InwardDetail::whereIn('id', $deleteIds)->delete();

        foreach ($request->inward_details as $detail) {

            if (!empty($detail['id'])) {
                // 🔄 UPDATE EXISTING
                InwardDetail::where('id', $detail['id'])->update([
                    'item_id'      => $detail['item_id'],
                    'yarn_type_id' => $detail['yarn_type_id'],
                    'shade'        => $detail['shade'] ?? null,
                    'bag_no'       => $detail['bag_no'] ?? null,
                    'gross_weight' => $detail['gross_weight'] ?? 0,
                    'tare_weight'  => $detail['tare_weight'] ?? 0,
                    'net_weight'   => $detail['net_weight'] ?? 0,
                    'uom'          => $detail['uom'] ?? null,
                    'remarks'      => $detail['remarks'] ?? null,
                    
                ]);

            } else {
                // ➕ NEW ROW
                InwardDetail::create([
                    'inward_id'   => $inward->id,
                    'user_id'     => $input['user_id'],
                    'item_id'     => $detail['item_id'],
                    'yarn_type_id'=> $detail['yarn_type_id'],
                    'shade'       => $detail['shade'] ?? null,
                    'bag_no'      => $detail['bag_no'] ?? null,
                    'gross_weight'=> $detail['gross_weight'] ?? 0,
                    'tare_weight' => $detail['tare_weight'] ?? 0,
                    'net_weight'  => $detail['net_weight'] ?? 0,
                    'uom'         => $detail['uom'] ?? null,
                    'remarks'     => $detail['remarks'] ?? null,
                    'job_card_id' => null, // ✅ NEW rows only
                ]);
            }
        }

        return response()->json($inward);
    }

    public function linkJobCard(Request $request, $id)
    {
        $detail = InwardDetail::findOrFail($id);

        if ($detail->job_card_id) {
            return response()->json([
                'message' => 'Job Card already linked'
            ], 403);
        }

        $detail->update([
            'job_card_id' => $request->job_card_id
        ]);

        return response()->json($detail);
    }

    public function InwardSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = Inward::with(['customer:id,customer_name', 'mill:id,mill_name'])
            ->select(
                'id',
                'inward_no',
                'customer_id',
                'mill_id',
                'total_weight',
                'remarks'
            );

        if ($search) {
            $query->where('inward_no', 'like', "%{$search}%");
        }

        return response()->json($query->get());
    }
}
