<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingRework;
use App\Models\KnittingProductionReturn;
use Illuminate\Support\Facades\DB;

class KnittingReworkController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $input = $request->all();
        $count = $input['limit'] ?? 10;
        $page  = $input['curpage'] ?? 1;
        $search = $input['searchInput'] ?? '';

        $query = KnittingRework::with(['productionReturn','jobMaster']);

        if(!empty($search)){
            $query->where('rework_no','LIKE',"%$search%");
        }

        return response([
            'data'=>$query->orderBy('id','desc')
                          ->take($count)
                          ->skip($count*($page-1))
                          ->get(),
            'total'=>$query->count()
        ]);
    }

    // CREATE REWORK NO
    public function reworkCreate()
    {
        $prefix = 'REW/' . date('Y') . '/';
        $last = \App\Models\KnittingRework::where('rework_no', 'like', $prefix . '%')
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

    // STORE
    public function store(Request $request)
    {
        $returnId = $request->production_return_id;

        // 🔒 VALIDATION: Rework ≤ Returned
        $returnedQty = KnittingProductionReturn::where('id',$returnId)
                            ->value('return_weight');

        $alreadyReworked = KnittingRework::where('production_return_id',$returnId)
                                ->sum('rework_weight');

        if(($request->rework_weight + $alreadyReworked) > $returnedQty){
            return response(['error'=>'Rework quantity exceeds returned quantity'],422);
        }

        DB::transaction(function() use ($request,&$data){
            $data = KnittingRework::create([
                'rework_no'            => $this->reworkCreate()->getData()->next_rework_no,
                'rework_date'          => $request->rework_date,
                'production_return_id' => $request->production_return_id,
                'job_card_id'          => $request->job_card_id ?? null,
                'rework_weight'           => $request->rework_weight,
                'remarks'              => $request->remarks ?? null,
                'user_id'              => $request->user_id ?? null,
            ]);
        });

        return response($data);
    }

    // EDIT
    public function edit($id)
    {
        return response(
            KnittingRework::with(['productionReturn','jobCard'])->findOrFail($id)
        );
    }

    // UPDATE
    public function update(Request $request,$id)
    {
        $returnId = $request->production_return_id;

        $returnedQty = KnittingProductionReturn::where('id',$returnId)
                            ->value('return_weight');

        $alreadyReworked = KnittingRework::where('production_return_id',$returnId)
                                ->where('id','!=',$id)
                                ->sum('rework_weight');

        if(($request->rework_weight + $alreadyReworked) > $returnedQty){
            return response(['error'=>'Rework quantity exceeds returned quantity'],422);
        }

        DB::transaction(function() use ($request,$id,&$rework){
            $rework = KnittingRework::findOrFail($id);
            $rework->update($request->all());
        });

        return response($rework);
    }

    // SELECT LIST
    public function selectList(Request $request)
    {
        $search = $request->input('q');

        $query = KnittingRework::with([
                'productionReturn:id,return_no',
                'jobCard:id,job_card_no'
            ])
            ->select('id','rework_no','production_return_id','job_card_id','rework_weight');

        if($search){
            $query->where('rework_no','LIKE',"%$search%");
        }

        return response()->json($query->get());
    }
}
