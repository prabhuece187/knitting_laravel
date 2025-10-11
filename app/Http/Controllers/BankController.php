<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use DB;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bank = $request->all();

        $count = $bank['limit'] ?? 10;
        $page  = $bank['curpage'] ?? 1;

        $data = DB::table('banks');

        $total = $data->count();

        $data = $data->take($count)
                    ->skip($count * ($page - 1))
                    ->orderBy('banks.id', 'desc')
                    ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // If the new bank is set as default, reset others
        if (!empty($input['is_default']) && $input['is_default']) {
            Bank::query()->update(['is_default' => false]);
        }

        $bank = Bank::create($input);

        return response()->json($bank);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bank = Bank::find($id);

        return response($bank);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bank = Bank::find($id);

        return response($bank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bank = Bank::findOrFail($id);
        $input = $request->all();

        // If updating this bank to default, unset default from others
        if (!empty($input['is_default']) && $input['is_default']) {
            Bank::where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $bank->update($input);

        return response()->json($bank);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bank = Bank::find($id);
        $bank->delete();

        return response($bank);
    }

    /**
     * Searchable list for dropdowns.
     */
    public function BankSelectList(Request $request)
    {
        $search = $request->input('q');
        
        $query = DB::table('banks')->select('id', 'bank_name', 'account_number','is_default');

        if ($search) {
            $query->where('bank_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    /**
     * Single bank data by ID.
     */
    public function SingleBankData(Request $request)
    {
        $query = DB::table('banks')
                    ->where('banks.is_default', true);

        return response()->json($query->first());
    }

    public function setDefault(Request $request, $id)
    {
        // Find the bank
        $bank = Bank::findOrFail($id);

        // Validate input
        $request->validate([
            'is_default' => 'required|boolean',
        ]);

        // If setting this bank as default, unset default for others
        if ($request->is_default) {
            Bank::where('id', '!=', $id)->update(['is_default' => false]);
        }

        // Update only the is_default field
        $bank->is_default = $request->is_default;
        $bank->save();

        return response()->json([
            'success' => true,
            'bank' => $bank,
        ]);
    }
}
