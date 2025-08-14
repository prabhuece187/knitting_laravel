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
    public function index(Request $request)
    {
        $customer = $request->all();

        if($customer['searchInput'] === "")
        {
            $count = $customer['limit'];
            $page  = $customer['curpage'];

            $sorting = "desc";

  	    $data = DB::table('customers')
	            ->leftJoin('states', 'customers.state_id', '=', 'states.id')
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
            $total = $data->count();

            $data = $data->take($count)
                    ->skip($count*($page-1))
                    ->orderby('customers.id','desc')
                    ->get();
        }
        else
        {
            $count = $customer['limit'];
		    $page  = $customer['curpage'];

		    $sorting = "desc";

        	$datas = DB::table('customers')
                     ->leftJoin('states', 'customers.state_id', '=', 'states.id')
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
                    )
                    ->where('customers.id','LIKE', '%' . $customer['searchInput'] . '%')
			        ->orWhere('customers.customer_gst_no','LIKE', '%' . $customer['searchInput'] . '%')
			        ->orWhere('customers.customer_mobile','LIKE', '%' . $customer['searchInput'] . '%')
			        ->orWhere('customers.customer_email','LIKE', '%' . $customer['searchInput'] . '%')
			        ->orWhere('customers.customer_address','LIKE', '%' . $customer['searchInput'] . '%')
 				    ->orWhere('states.state_name', 'LIKE', '%' . $customer['searchInput'] . '%')
              		->orWhere('customers.state_id', 'LIKE', '%' . $customer['searchInput'] . '%');


        	$total = $datas->count();

        	$data = $datas->take($count)
                	->skip($count*($page-1))
			        ->orderby('customers.id','desc')
                	->get();

	    }
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
        $customer = Customer::with('state')->find($id);

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

    public function CustomerSelectList(Request $request)
    {
        $search = $request->input('q');
        
        $query = DB::table('customers')->select('id', 'customer_name','customer_mobile');

        if ($search) {
            $query->where('customer_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

    public function SingleCustomerData(Request $request,$id)
    { 
        $query = DB::table('customers')->select('id', 'customer_name','customer_mobile')->where('customers.id',$id);

        return response()->json($query->first());
    }

    
}
