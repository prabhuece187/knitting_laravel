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
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = DB::table('banks')
            ->where('banks.user_id', Auth::id())
            ->select(
                'banks.id',
                'banks.user_id',
                'banks.bank_name',
                'banks.account_number',
                'banks.ifsc_code',
                'banks.branch_name',
                'banks.created_at',
                'banks.updated_at'
            );

        // 🔍 Search (same pattern as customer)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('banks.bank_name', 'like', "%{$search}%")
                    ->orWhere('banks.account_number', 'like', "%{$search}%")
                    ->orWhere('banks.ifsc_code', 'like', "%{$search}%")
                    ->orWhere('banks.branch_name', 'like', "%{$search}%");
            });
        }

        // ✅ Same order style
        $query->orderBy('banks.id', 'desc');

        // ✅ Same pagination method as customer
        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $input['user_id'] = Auth::id();

            // Handle default bank logic
            if (!empty($input['is_default']) && $input['is_default']) {
                Bank::where('user_id', Auth::id())
                    ->update(['is_default' => false]);
            }

            $bank = Bank::create($input);

            return response()->json([
                'message' => 'Bank Added Successfully',
                'data' => $bank
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to Add Bank',
                'error' => $e->getMessage() // optional (remove in production)
            ], 500);
        }
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
        try {
            $bank = Bank::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $input = $request->all();

            // Handle default bank logic
            if (!empty($input['is_default']) && $input['is_default']) {
                Bank::where('user_id', Auth::id())
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $bank->update($input);

            return response()->json([
                'message' => 'Bank Updated Successfully',
                'data' => $bank
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to Update Bank',
                'error' => $e->getMessage() // optional (remove in production)
            ], 500);
        }
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