<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mill;

class MillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Mill::select('*')
        ->get();

        return response($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mill = $request->all();
        $mill = Mill::create($mill);

		return response($mill);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $mill = $request->all();
        $mill = Mill::create($mill);

        return response($mill);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $mill = Mill::find($id);

        return response($mill);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mill = Mill::find($id);

        return response($mill);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $mill = Mill::find($id);
        $input = $request->all();
        $mill->update($input);

        return response($mill);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mill = Mill::find($id);
        $mill->delete();

        return response($mill);
    }
}