<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingRework;
use App\Models\KnittingProductionReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KnittingReworkController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = KnittingRework::with(['productionReturn','jobMaster'])
            ->select('knitting_reworks.*');

        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('knitting_reworks.rework_no', 'like', "%{$search}%")
                ->orWhere('knitting_reworks.id', 'like', "%{$search}%");

                $q->orWhereHas('productionReturn', function ($qp) use ($search) {
                    $qp->where('return_no', 'like', "%{$search}%");
                });

                $q->orWhereHas('jobMaster', function ($qj) use ($search) {
                    $qj->where('job_card_no', 'like', "%{$search}%");
                });

            });

        }

        $query->orderBy('knitting_reworks.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    public function reworkCreate()
    {
        $prefix = 'REW/' . date('Y') . '/';
        $last = KnittingRework::where('rework_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('rework_no');

        if (!$last) {
            $next = $prefix . '0001';
        } else {
            $number = (int)substr($last, strlen($prefix));
            $next = $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        }

        return response()->json(['next_rework_no' => $next]);
    }

    // public function store(Request $request)
    // {

    //     $returnId = $request->production_return_id;

    //     $returnedQty = KnittingProductionReturn::where('id',$returnId)
    //                         ->value('return_weight');

    //     $alreadyReworked = KnittingRework::where('production_return_id',$returnId)
    //                             ->sum('rework_weight');

    //     if(($request->rework_weight + $alreadyReworked) > $returnedQty){
    //         return response(['error'=>'Rework quantity exceeds returned quantity'],422);
    //     }

    //     DB::transaction(function() use ($request,&$data){

    //         $data = KnittingRework::create([
    //             'rework_no'            => $this->reworkCreate()->getData()->next_rework_no,
    //             'rework_date'          => $request->rework_date,
    //             'production_return_id' => $request->production_return_id,
    //             'job_card_id'          => $request->job_card_id ?? null,
    //             'rework_weight'        => $request->rework_weight,
    //             'remarks'              => $request->remarks ?? null,
    //             'user_id'              => Auth::id(),
    //         ]);

    //     });

    //     return response($data);
    // }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $returnId = $request->production_return_id;

            $returnedQty = KnittingProductionReturn::where('id', $returnId)
                ->value('return_weight');

            $alreadyReworked = KnittingRework::where('production_return_id', $returnId)
                ->sum('rework_weight');

            if (($request->rework_weight + $alreadyReworked) > $returnedQty) {
                return response()->json([
                    'message' => 'Rework quantity exceeds returned quantity'
                ], 422);
            }

            $data = KnittingRework::create([
                'rework_no'            => $this->reworkCreate()->getData()->next_rework_no,
                'rework_date'          => $request->rework_date,
                'production_return_id' => $request->production_return_id,
                'job_card_id'          => $request->job_card_id ?? null,
                'rework_weight'        => $request->rework_weight,
                'remarks'              => $request->remarks ?? null,
                'user_id'              => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Rework Created Successfully',
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating rework',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        return response(
            KnittingRework::with(['productionReturn','jobCard'])->findOrFail($id)
        );
    }

    // public function update(Request $request,$id)
    // {

    //     $returnId = $request->production_return_id;

    //     $returnedQty = KnittingProductionReturn::where('id',$returnId)
    //                         ->value('return_weight');

    //     $alreadyReworked = KnittingRework::where('production_return_id',$returnId)
    //                             ->where('id','!=',$id)
    //                             ->sum('rework_weight');

    //     if(($request->rework_weight + $alreadyReworked) > $returnedQty){
    //         return response(['error'=>'Rework quantity exceeds returned quantity'],422);
    //     }

    //     DB::transaction(function() use ($request,$id,&$rework){

    //         $rework = KnittingRework::findOrFail($id);
    //         $rework->update($request->all());

    //     });

    //     return response($rework);
    // }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $returnId = $request->production_return_id;

            $returnedQty = KnittingProductionReturn::where('id', $returnId)
                ->value('return_weight');

            $alreadyReworked = KnittingRework::where('production_return_id', $returnId)
                ->where('id', '!=', $id)
                ->sum('rework_weight');

            if (($request->rework_weight + $alreadyReworked) > $returnedQty) {
                return response()->json([
                    'message' => 'Rework quantity exceeds returned quantity'
                ], 422);
            }

            $rework = KnittingRework::where('user_id', auth()->id())
                ->findOrFail($id);

            $rework->update([
                'rework_date'          => $request->rework_date,
                'production_return_id' => $request->production_return_id,
                'job_card_id'          => $request->job_card_id ?? null,
                'rework_weight'        => $request->rework_weight,
                'remarks'              => $request->remarks ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Rework Updated Successfully',
                'data'    => $rework
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error updating rework',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}