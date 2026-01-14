<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingMachine;
use DB;

class KnittingMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $input = $request->all();

        $count = $input['limit'] ?? 10;
        $page  = $input['curpage'] ?? 1;

        $data = DB::table('knitting_machines');

        $total = $data->count();

        $data = $data->take($count)
                    ->skip($count * ($page - 1))
                    ->orderBy('knitting_machines.id', 'desc')
                    ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $machine = KnittingMachine::create($input);

        return response()->json($machine);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $machine = KnittingMachine::find($id);

        return response($machine);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $machine = KnittingMachine::find($id);

        return response($machine);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $machine = KnittingMachine::findOrFail($id);
        $input   = $request->all();

        $machine->update($input);

        return response()->json($machine);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(string $id)
    {
        $machine = KnittingMachine::find($id);
        $machine->delete();

        return response($machine);
    }

    /**
     * Searchable list for dropdowns (Machine No).
     */
    public function MachineSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('knitting_machines')
                    ->select(
                        'id',
                        'machine_no',
                        'machine_name',
                        'dia',
                        'gauge',
                        'status'
                    )
                    ->where('status', 'active');

        if ($search) {
            $query->where('machine_no', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    /**
     * Get single machine data by ID.
     */
    public function SingleMachineData(Request $request, $id)
    {
        $machine = DB::table('knitting_machines')
                    ->where('id', $id)
                    ->first();

        return response()->json($machine);
    }
}
