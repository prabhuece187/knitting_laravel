<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingMachine;
use Illuminate\Support\Facades\Auth;
use DB;

class KnittingMachineController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int)$request->get('page',1);
        $limit  = (int)$request->get('limit',10);
        $search = $request->get('searchInput');

        $query = DB::table('knitting_machines')
            ->where('user_id',Auth::id())
            ->select(
                'id',
                'user_id',
                'machine_no',
                'brand',
                'dia',
                'gauge',
                'status',
                'created_at',
                'updated_at'
            );

        if(!empty($search)){
            $query->where(function ($q) use ($search){
                $q->where('machine_no','like',"%{$search}%")
                  ->orWhere('brand','like',"%{$search}%")
                  ->orWhere('dia','like',"%{$search}%")
                  ->orWhere('gauge','like',"%{$search}%")
                  ->orWhere('status','like',"%{$search}%");
            });
        }

        $query->orderBy('id','desc');

        return response()->json(
            $this->paginate($query,$page,$limit)
        );
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $input['user_id'] = Auth::id();

        $machine = KnittingMachine::create($input);

        return response()->json($machine);
    }

    public function show($id)
    {
        return KnittingMachine::where('user_id',Auth::id())->findOrFail($id);
    }

    public function update(Request $request,$id)
    {
        $machine = KnittingMachine::where('user_id',Auth::id())->findOrFail($id);

        $machine->update($request->all());

        return response()->json($machine);
    }

    public function destroy($id)
    {
        $machine = KnittingMachine::where('user_id',Auth::id())->findOrFail($id);

        $machine->delete();

        return response($machine);
    }

    public function MachineSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('knitting_machines')
            ->where('user_id',Auth::id())
            ->where('status','active')
            ->select(
                'id',
                'machine_no',
                'machine_name',
                'dia',
                'gauge',
                'status'
            );

        if($search){
            $query->where('machine_no','like',"%$search%");
        }

        return response()->json($query->get());
    }

    public function SingleMachineData(Request $request,$id)
    {
        $machine = DB::table('knitting_machines')
            ->where('user_id',Auth::id())
            ->where('id',$id)
            ->first();

        return response()->json($machine);
    }
}