<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use DB;

class ItemController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('searchInput');

        $query = DB::table('items')
            ->where('items.user_id', Auth::id())
            ->select(
                'id',
                'user_id',
                'item_name',
                'hsn_code',
                'unit',
                'description',
                'price',
                'barcode',
                'qrcode',
                'created_at',
                'updated_at'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('hsn_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $query->orderBy('id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    /**
     * Store Item
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // attach logged user id
        $input['user_id'] = Auth::id();

        $item = Item::create($input);

        return response()->json($item);
    }

    /**
     * Show Item
     */
    public function show(string $id)
    {
        $item = Item::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($item);
    }

    /**
     * Edit Item
     */
    public function edit(string $id)
    {
        $item = Item::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($item);
    }

    /**
     * Update Item
     */
    public function update(Request $request, string $id)
    {
        $item = Item::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $item->update($request->all());

        return response()->json($item);
    }

    /**
     * Delete Item
     */
    public function destroy(string $id)
    {
        $item = Item::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $item->delete();

        return response()->json($item);
    }

    /**
     * Dropdown list
     */
    public function ItemSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('items')
            ->where('user_id', Auth::id())
            ->select('id', 'item_name', 'hsn_code');

        if ($search) {
            $query->where('item_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    /**
     * Single Item
     */
    public function SingleItemData(Request $request, $id)
    {
        $query = DB::table('items')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->select('id', 'item_name', 'hsn_code');

        return response()->json($query->first());
    }
}