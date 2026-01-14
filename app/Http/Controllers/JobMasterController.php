<?php

namespace App\Http\Controllers;

use App\Models\JobMaster;
use App\Models\KnittingJobLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobMasterController extends Controller
{
    // List
    public function index()
    {
        return JobMaster::with(['inward', 'customer', 'mill'])->orderBy('id', 'desc')->get();
    }

    // Show single
    public function show($id)
    {
        return JobMaster::with(['inward', 'customer', 'mill'])->findOrFail($id);
    }

    // Generate next job number: JOB/2025/0001 style
    public function nextJobNo()
    {
        $prefix = 'JOB/' . date('Y') . '/';
        $last = JobMaster::where('job_card_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('job_card_no');

        if (! $last) {
            $next = $prefix . str_pad(1, 4, '0', STR_PAD_LEFT);
        } else {
            $lastNum = (int)substr($last, strrpos($last, '/') + 1);
            $next = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return response()->json(['job_no' => $next]);
    }

    // Create
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_card_no' => 'required|string|unique:job_masters,job_card_no',
            'job_date' => 'required|date',

            'inward_id' => 'required|exists:inwards,id',
            'customer_id' => 'required|exists:customers,id',
            'mill_id' => 'required|exists:mills,id',

            'approx_job_weight' => 'nullable|numeric',
            'expected_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'user_id' => 'required|exists:users,id',    
        ]);

        DB::beginTransaction();

        try {
            $job = JobMaster::create([
                ...$data,
                'status' => 'open',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Job created successfully',
                'data' => $job,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update
    public function update(Request $request, $id)
    {
        $job = JobMaster::findOrFail($id);

        $data = $request->validate([
            'job_date' => 'required|date',

            'inward_id' => 'required|exists:inwards,id',
            'customer_id' => 'required|exists:customers,id',
            'mill_id' => 'required|exists:mills,id',

            'approx_job_weight' => 'nullable|numeric',
            'expected_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',

            'status' => 'required|in:open,completed,cancelled',
            'user_id' => 'required|exists:users,id', 
        ]);

        DB::beginTransaction();

        try {
            $job->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Job updated successfully',
                'data' => $job,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error updating job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Delete
    public function destroy($id)
    {
        $job = JobMaster::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Deleted']);
    }

       /**
     * Searchable list for dropdowns.
     */
    public function JobSelectList(Request $request)
    {
        $search = $request->input('q');
        
        $query = DB::table('job_masters')->select('id', 'job_card_no');

        if ($search) {
            $query->where('machine_name', 'like', "%$search%");
        }

        return response()->json($query->get());
    }

}
