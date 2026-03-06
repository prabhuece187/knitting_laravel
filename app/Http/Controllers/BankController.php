<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use DB;

class BankController extends BaseController
{

    public function index(Request $request)
    {
        $count = $request->limit ?? 10;
        $page  = $request->curpage ?? 1;

        $data = DB::table('banks')
            ->where('user_id', Auth::id());

        $total = $data->count();

        $data = $data->take($count)
            ->skip($count * ($page - 1))
            ->orderBy('id', 'desc')
            ->get();

        return response(['data' => $data, 'total' => $total]);
    }


    public function store(Request $request)
    {
        $input = $request->all();

        $input['user_id'] = Auth::id();

        if (!empty($input['is_default']) && $input['is_default']) {
            Bank::where('user_id', Auth::id())
                ->update(['is_default' => false]);
        }

        $bank = Bank::create($input);

        return response()->json($bank);
    }


    public function show(string $id)
    {
        $bank = Bank::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response($bank);
    }


    public function edit(string $id)
    {
        $bank = Bank::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response($bank);
    }


    public function update(Request $request, string $id)
    {
        $bank = Bank::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $input = $request->all();

        if (!empty($input['is_default']) && $input['is_default']) {
            Bank::where('user_id', Auth::id())
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $bank->update($input);

        return response()->json($bank);
    }


    public function destroy(string $id)
    {
        $bank = Bank::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $bank->delete();

        return response($bank);
    }


    public function BankSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('banks')
            ->select('id', 'bank_name', 'account_number', 'is_default')
            ->where('user_id', Auth::id());

        if ($search) {
            $query->where('bank_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }


    public function SingleBankData()
    {
        $bank = DB::table('banks')
            ->where('user_id', Auth::id())
            ->where('is_default', true)
            ->first();

        return response()->json($bank);
    }


    public function setDefault(Request $request, $id)
    {
        $bank = Bank::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($request->is_default) {
            Bank::where('user_id', Auth::id())
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $bank->is_default = $request->is_default;
        $bank->save();

        return response()->json([
            'success' => true,
            'bank' => $bank,
        ]);
    }
}