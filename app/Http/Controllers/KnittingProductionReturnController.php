<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingProductionReturn;
use App\Models\KnittingProductionDetail;
use Illuminate\Support\Facades\DB;

class KnittingProductionReturnController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $input = $request->all();
        $count = $input['limit'] ?? 10;
        $page  = $input['curpage'] ?? 1;
        $search = $input['searchInput'] ?? '';

        $query = KnittingProductionReturn::with(['jobMaster','production']);

        if(!empty($search)){
            $query->where('return_no','LIKE',"%$search%")
                  ->orWhereHas('jobMaster', fn($q) =>
                      $q->where('job_card_no','LIKE',"%$search%")
                  );
        }

        return response([
            'data' => $query->orderBy('id','desc')
                            ->take($count)
                            ->skip($count*($page-1))
                            ->get(),
            'total' => $query->count()
        ]);
    }

    // CREATE RETURN NO
    public function returnCreate()
    {
        $prefix = 'RET/' . date('Y') . '/';
        $last = \App\Models\KnittingProductionReturn::where('return_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('return_no');

        if (!$last) {
            $next = $prefix . '0001';
        } else {
            $number = (int)substr($last, strlen($prefix));
            $next = $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        }

        return response()->json(['next_return_no' => $next]);
    }

    // STORE
   public function store(Request $request)
    {
        $jobCardId = $request->job_card_id;

        $productionRetrunNo = $this->returnCreate()->getData()->next_return_no;

        // 🔒 VALIDATION: Return ≤ Produced
        $producedQty = KnittingProductionDetail::whereHas('production', function ($q) use ($jobCardId) {
            $q->where('job_card_id', $jobCardId);
        })->sum('produced_weight');

        $alreadyReturned = KnittingProductionReturn::where('job_card_id', $jobCardId)
            ->sum('return_weight');

        if (($request->return_weight + $alreadyReturned) > $producedQty) {
            return response(['error' => 'Return quantity exceeds produced quantity'], 422);
        }

        DB::transaction(function () use ($request, $productionRetrunNo, &$data) {
            $data = KnittingProductionReturn::create([
                'return_no'        => $productionRetrunNo,
                'return_date'      => $request->return_date,
                'job_card_id'      => $request->job_card_id,
                'production_id'    => $request->production_id ?? null,
                'return_weight'    => $request->return_weight,
                'return_reason'    => $request->return_reason,
                'rework_required'  => $request->rework_required ?? false,
                'remarks'          => $request->remarks ?? null,
                'user_id'         => $request->user_id ?? null,
            ]);
        });

        return response($data);
    }


    // EDIT
    public function edit($id)
    {
        return response(
            KnittingProductionReturn::with(['jobCard','production','reworks'])->findOrFail($id)
        );
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $jobCardId = $request->job_card_id;

        $producedQty = KnittingProductionDetail::whereHas('production', function($q) use ($jobCardId){
            $q->where('job_card_id',$jobCardId);
        })->sum('produced_weight');

        $alreadyReturned = KnittingProductionReturn::where('job_card_id',$jobCardId)
                                ->where('id','!=',$id)
                                ->sum('return_weight');

        if(($request->return_weight + $alreadyReturned) > $producedQty){
            return response(['error'=>'Return quantity exceeds produced quantity'],422);
        }

        DB::transaction(function() use ($request,$id,&$return){
            $return = KnittingProductionReturn::findOrFail($id);
            $return->update($request->all());
        });

        return response($return);
    }

    // SELECT LIST
    public function selectList(Request $request)
    {
        $search = $request->input('q');

        $query = KnittingProductionReturn::with(['jobMaster:id,job_card_no'])
            ->select('id','return_no','job_card_id','return_weight');

        if($search){
            $query->where('return_no','LIKE',"%$search%");
        }

        return response()->json($query->get());
    }
}
