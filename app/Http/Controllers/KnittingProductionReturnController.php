<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingProductionReturn;
use App\Models\KnittingProductionDetail;
use Illuminate\Support\Facades\DB;

class KnittingProductionReturnController extends BaseController
{
    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = KnittingProductionReturn::with(['jobMaster','production'])
            ->select('knitting_production_returns.*')
            ->where('user_id', auth()->id());

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                $q->where('knitting_production_returns.return_no', 'like', "%{$search}%")
                ->orWhere('knitting_production_returns.id', 'like', "%{$search}%")
                ->orWhere('knitting_production_returns.return_reason', 'like', "%{$search}%");

                $q->orWhereHas('jobMaster', function ($qj) use ($search) {
                    $qj->where('job_card_no', 'like', "%{$search}%");
                });

                $q->orWhereHas('production', function ($qp) use ($search) {
                    $qp->where('production_no', 'like', "%{$search}%");
                });
            });
        }

        $query->orderBy('knitting_production_returns.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    public function returnCreate()
    {
        $prefix = 'RET/' . date('Y') . '/';

        $last = KnittingProductionReturn::where('return_no', 'like', $prefix . '%')
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

    public function store(Request $request)
    {
        $jobCardId = $request->job_card_id;

        $productionRetrunNo = $this->returnCreate()->getData()->next_return_no;

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
                'user_id'          => auth()->id(),
            ]);
        });

        return response($data);
    }

    public function edit($id)
    {
        return response(
            KnittingProductionReturn::with(['jobCard','production','reworks'])
                ->where('user_id', auth()->id())
                ->findOrFail($id)
        );
    }

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

            $return = KnittingProductionReturn::where('user_id', auth()->id())
                        ->findOrFail($id);

            $data = $request->all();
            $data['user_id'] = auth()->id();

            $return->update($data);
        });

        return response($return);
    }

    public function selectList(Request $request)
    {
        $search = $request->input('q');

        $query = KnittingProductionReturn::with(['jobMaster:id,job_card_no'])
            ->select('id','return_no','job_card_id','return_weight')
            ->where('user_id', auth()->id());

        if($search){
            $query->where('return_no','LIKE',"%$search%");
        }

        return response()->json($query->get());
    }
}