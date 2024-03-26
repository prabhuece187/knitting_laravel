<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YarnType;

class YarnTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = YarnType::select('*')
        ->get();

        return response($data);
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
