<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YarnType;
use DB;

class YarnTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $yarn = $request->all();

        $count = $yarn['limit'];
        $page  = $yarn['curpage'];

        $sorting = "desc";

        $data = DB::table('yarn_types');

        $total = $data->count();

        $data = $data->take($count)
                ->skip($count*($page-1))
                ->orderby('yarn_types.id','desc')
                ->get();

        return response(['data' => $data , 'total' => $total]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $yarntype = $request->all();
        $yarntype = YarnType::create($yarntype);

		return response($yarntype);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $yarntype = $request->all();
        $yarntype = YarnType::create($yarntype);

        return response($yarntype);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $yarntype = YarnType::find($id);

        return response($yarntype);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $yarntype = YarnType::find($id);

        return response($yarntype);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $yarntype = YarnType::find($id);
        $input = $request->all();
        $yarntype->update($input);

        return response($yarntype);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $yarntype = YarnType::find($id);
        $yarntype->delete();

        return response($yarntype);
    }
}
