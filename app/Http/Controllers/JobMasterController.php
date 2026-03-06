<?php

namespace App\Http\Controllers;

use App\Models\JobMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobMasterController extends BaseController
{
    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = JobMaster::with(['inward', 'customer', 'mill'])
            ->where('job_masters.user_id', Auth::id())
            ->select('job_masters.*');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                $q->where('job_masters.job_card_no', 'like', "%{$search}%")
                ->orWhere('job_masters.status', 'like', "%{$search}%")
                ->orWhere('job_masters.id', 'like', "%{$search}%");

                $q->orWhereHas('inward', function ($qi) use ($search) {
                    $qi->where('inward_no', 'like', "%{$search}%");
                });

                $q->orWhereHas('customer', function ($qc) use ($search) {
                    $qc->where('customer_name', 'like', "%{$search}%");
                });

                $q->orWhereHas('mill', function ($qm) use ($search) {
                    $qm->where('mill_name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderBy('job_masters.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

    // Show single
    public function show($id)
    {
        return JobMaster::with(['inward','customer','mill'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
    }

    // Generate next job number
    public function nextJobNo()
    {
        $prefix = 'JOB/' . date('Y') . '/';

        $last = JobMaster::where('user_id', Auth::id())
            ->where('job_card_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('job_card_no');

        if (!$last) {
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
        ]);

        DB::beginTransaction();

        try {

            $job = JobMaster::create([
                ...$data,
                'user_id' => Auth::id(),
                'status' => 'open'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Job created successfully',
                'data' => $job
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error creating job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update
    public function update(Request $request, $id)
    {
        $job = JobMaster::where('user_id', Auth::id())
            ->findOrFail($id);

        $data = $request->validate([
            'job_date' => 'required|date',

            'inward_id' => 'required|exists:inwards,id',
            'customer_id' => 'required|exists:customers,id',
            'mill_id' => 'required|exists:mills,id',

            'approx_job_weight' => 'nullable|numeric',
            'expected_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',

            'status' => 'required|in:open,completed,cancelled'
        ]);

        DB::beginTransaction();

        try {

            $job->update($data);

            DB::commit();

            return response()->json([
                'message' => 'Job updated successfully',
                'data' => $job
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error updating job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete
    public function destroy($id)
    {
        $job = JobMaster::where('user_id', Auth::id())
            ->findOrFail($id);

        $job->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }

    /**
     * Dropdown list
     */
    public function JobSelectList(Request $request)
    {
        $search = $request->input('q');

        $query = DB::table('job_masters')
            ->where('user_id', Auth::id())
            ->select('id','job_card_no');

        if ($search) {
            $query->where('job_card_no','like',"%$search%");
        }

        return response()->json($query->get());
    }
}