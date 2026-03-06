<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YarnType;
use Illuminate\Support\Facades\Auth;
use DB;

class YarnTypeController extends BaseController
{

    public function index(Request $request)
    {
        $page  = (int)$request->get('page',1);
        $limit = (int)$request->get('limit',10);
        $search = $request->get('searchInput');

        $query = DB::table('yarn_types')
            ->where('user_id',Auth::id())
            ->select('id','user_id','yarn_type','created_at','updated_at');

        if(!empty($search)){
            $query->where('yarn_type','like',"%{$search}%");
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

        $yarntype = YarnType::create($input);

        return response($yarntype);
    }

    public function show($id)
    {
        return YarnType::where('user_id',Auth::id())->findOrFail($id);
    }

    public function update(Request $request,$id)
    {
        $yarntype = YarnType::where('user_id',Auth::id())->findOrFail($id);

        $yarntype->update($request->all());

        return response($yarntype);
    }

    public function destroy($id)
    {
        $yarntype = YarnType::where('user_id',Auth::id())->findOrFail($id);

        $yarntype->delete();

        return response($yarntype);
    }

    public function YarnTypeSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('yarn_types')
            ->where('user_id',Auth::id())
            ->select('id','yarn_type');

        if($search){
            $query->where('yarn_type','like',"%$search%");
        }

        return response()->json($query->get());
    }
}