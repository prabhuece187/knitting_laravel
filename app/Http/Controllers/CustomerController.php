<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\User;
use DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customer = $request->all();

        $count = $customer['limit'];
        $page  = $customer['curpage'];

        $sorting = "desc";

        $data = DB::table('customers');

        $total = $data->count();

        $data = $data->take($count)
                ->skip($count*($page-1))
                ->orderby('customers.id','desc')
                ->get();

        return response(['data' => $data , 'total' => $total]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = $request->all();
        $customer = Customer::create($customer);

		return response($customer);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $customer = $request->all();
        $customer['customer_state'] = $customer['customer_state']['label'];
        $customer = Customer::create($customer);

        return response($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $customer = Customer::find($id);

        return response($customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::find($id);

        return response($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::find($id);
        $input = $request->all();
        $customer->update($input);

        return response($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);
        $customer->delete();

        return response($customer);
    }
}
