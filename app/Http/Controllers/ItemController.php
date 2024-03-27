<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $item = $request->all();

        $count = $item['limit'];
        $page  = $item['curpage'];

        $sorting = "desc";

        $data = DB::table('items');

        $total = $data->count();

        $data = $data->take($count)
                ->skip($count*($page-1))
                ->orderby('items.id','desc')
                ->get();

        return response(['data' => $data , 'total' => $total]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $item = $request->all();
        $item = Item::create($item);

		return response($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $item = $request->all();
        $item = Item::create($item);

        return response($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::find($id);

        return response($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Item::find($id);

        return response($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::find($id);
        $input = $request->all();
        $item->update($input);

        return response($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::find($id);
        $item->delete();

        return response($item);
    }
}
