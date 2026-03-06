<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use DB;

class CustomerController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = DB::table('customers')
            ->leftJoin('states', 'customers.state_id', '=', 'states.id')
            ->where('customers.user_id', Auth::id())
            ->select(
                'customers.id',
                'customers.user_id',
                'customers.customer_name',
                'customers.customer_mobile',
                'customers.customer_email',
                'customers.customer_address',
                'customers.customer_gst_no',
                'customers.state_id as customer_state_id',
                'customers.created_at',
                'customers.updated_at',
                'states.state_name'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.customer_name', 'like', "%{$search}%")
                    ->orWhere('customers.customer_mobile', 'like', "%{$search}%")
                    ->orWhere('customers.customer_email', 'like', "%{$search}%")
                    ->orWhere('customers.customer_gst_no', 'like', "%{$search}%")
                    ->orWhere('customers.customer_address', 'like', "%{$search}%")
                    ->orWhere('states.state_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('customers.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // automatically attach logged user id
        $input['user_id'] = Auth::id();

        $customer = Customer::create($input);

        return response()->json($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::with('state')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $customer->update($request->all());

        return response()->json($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $customer->delete();

        return response()->json($customer);
    }

    /**
     * Dropdown customer list
     */
    public function CustomerSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('customers')
            ->where('user_id', Auth::id())
            ->select('id', 'customer_name', 'customer_mobile');

        if ($search) {
            $query->where('customer_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    /**
     * Single customer dropdown data
     */
    public function SingleCustomerData(Request $request, $id)
    {
        $query = DB::table('customers')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->select('id', 'customer_name', 'customer_mobile');

        return response()->json($query->first());
    }
}