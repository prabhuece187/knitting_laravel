<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\State;
use DB;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $state = $request->all();

        $count = $state['limit'];
        $page  = $state['curpage'];

        $sorting = "desc";

        $data = DB::table('states');

        $total = $data->count();

        $data = $data->take($count)
                ->skip($count*($page-1))
                ->orderby('states.id','desc')
                ->get();

        return response(['data' => $data , 'total' => $total]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $state = $request->all();
        $state = State::create($state);

		return response($state);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $state = $request->all();
        $state = State::create($state);

        return response($state);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $state = State::find($id);

        return response($state);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $state = State::find($id);

        return response($state);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $state = State::find($id);
        $input = $request->all();
        $state->update($input);

        return response($state);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $state = State::find($id);
        $state->delete();

        return response($state);
    }

    public function StateSelectList(Request $request)
    {
        $search = $request->input('q');
        
        $query = DB::table('states')->select('id', 'state_name');

        if ($search) {
            $query->where('state_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }
}
