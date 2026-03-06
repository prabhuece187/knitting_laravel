<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mill;
use Illuminate\Support\Facades\Auth;
use DB;

class MillController extends BaseController
{
    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('searchInput');

        $query = DB::table('mills')
            ->where('user_id', Auth::id())
            ->select(
                'id',
                'user_id',
                'mill_name',
                'mobile_number',
                'address',
                'description',
                'created_at',
                'updated_at'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('mill_name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
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

        $mill = Mill::create($input);

        return response($mill);
    }

    public function show($id)
    {
        return Mill::where('user_id',Auth::id())->findOrFail($id);
    }

    public function edit($id)
    {
        return Mill::where('user_id',Auth::id())->findOrFail($id);
    }

    public function update(Request $request,$id)
    {
        $mill = Mill::where('user_id',Auth::id())->findOrFail($id);

        $mill->update($request->all());

        return response($mill);
    }

    public function destroy($id)
    {
        $mill = Mill::where('user_id',Auth::id())->findOrFail($id);

        $mill->delete();

        return response($mill);
    }

    public function MillSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('mills')
            ->where('user_id',Auth::id())
            ->select('id','mill_name','mobile_number');

        if ($search) {
            $query->where('mill_name','like',"%$search%");
        }

        return response()->json($query->get());
    }

    public function SingleMillData(Request $request,$id)
    {
        $query = DB::table('mills')
            ->where('user_id',Auth::id())
            ->where('id',$id)
            ->select('id','mill_name');

        return response()->json($query->first());
    }
}