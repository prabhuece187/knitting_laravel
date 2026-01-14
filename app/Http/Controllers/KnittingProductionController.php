<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingProduction;
use App\Models\KnittingProductionDetail;
use App\Models\JobMaster;
use App\Models\KnittingMachine;
use Illuminate\Support\Facades\DB;

class KnittingProductionController extends Controller
{
    // LIST / PAGINATION
    public function index(Request $request)
    {
        $input = $request->all();
        $count = $input['limit'] ?? 10;
        $page  = $input['curpage'] ?? 1;
        $search = $input['searchInput'] ?? '';
        $sorting = "desc";

        $query = KnittingProduction::with(['jobMaster', 'machine', 'details']);

        if (!empty($search)) {
            $query = $query->whereHas('jobMaster', function ($q) use ($search) {
                $q->where('job_card_no', 'LIKE', "%$search%");
            })->orWhereHas('machine', function ($q) use ($search) {
                $q->where('machine_name', 'LIKE', "%$search%");
            })->orWhere('production_no', 'LIKE', "%$search%");
        }

        $total = $query->count();

        $data = $query->orderBy('id', $sorting)
                    ->take($count)
                    ->skip($count * ($page - 1))
                    ->get();

        return response(['data' => $data, 'total' => $total]);
    }

    // CREATE – GET NEXT PRODUCTION NUMBER
    public function productionCreate()
    {
        $prefix = 'PROD/' . date('Y') . '/';

        $last = KnittingProduction::where('production_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('production_no');

        if (! $last) {
            $next = $prefix . str_pad(1, 4, '0', STR_PAD_LEFT);
        } else {
            $lastNum = (int) substr($last, strrpos($last, '/') + 1);
            $next = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'pro_no' => $next
        ]);
    }

    // STORE HEADER + DETAILS
    public function store(Request $request)
    {
        $input = $request->all();

        DB::transaction(function() use ($input, &$data) {
             $productionNo = $this->productionCreate()->getData()->pro_no; // generate production_no

            $data = KnittingProduction::create([
                'production_no'   => $productionNo,
                'production_date' => $input['production_date'],
                'job_card_id'     => $input['job_card_id'],
                'machine_id'      => $input['machine_id'] ?? null,
                'shift'           => $input['shift'] ?? null,
                'operator_name'   => $input['operator_name'] ?? null,
                'remarks'         => $input['remarks'] ?? null,
                'user_id'         => $input['user_id'] ?? null,
            ]);

            foreach ($input['details'] as $detail) {
                $detail['knitting_production_id'] = $data->id;
                KnittingProductionDetail::create($detail);
            }
        });

        return response($data);
    }

    // EDIT – GET HEADER + DETAILS
    public function edit($id)
    {
        $production = KnittingProduction::with(['jobMaster', 'machine'])->findOrFail($id);
        $production['details'] = KnittingProductionDetail::where('knitting_production_id', $id)->get();
        return response($production);
    }

    // UPDATE HEADER + DETAILS (DO NOT CHANGE production_no)
    public function update(Request $request, $id)
    {
        $input = $request->all();

        DB::transaction(function() use ($input, $id, &$production) {
            $production = KnittingProduction::findOrFail($id);

            // update header except production_no
            $production->update([
                'production_date' => $input['production_date'],
                'job_card_id'     => $input['job_card_id'],
                'machine_id'      => $input['machine_id'] ?? null,
                'shift'           => $input['shift'] ?? null,
                'operator_name'   => $input['operator_name'] ?? null,
                'remarks'         => $input['remarks'] ?? null,
                'user_id'         => $input['user_id'] ?? null,
            ]);

            // delete old details
            KnittingProductionDetail::where('knitting_production_id', $id)->delete();

            // insert new details
            foreach ($input['details'] as $detail) {
                $detail['knitting_production_id'] = $production->id;
                KnittingProductionDetail::create($detail);
            }
        });

        return response($production);
    }

    // SELECT LIST – FOR DROPDOWNS
    public function selectList(Request $request)
    {
        $search = $request->input('q');

        $query = KnittingProduction::with(['jobMaster:id,job_card_no', 'machine:id,machine_name'])
            ->select('id', 'production_no', 'job_card_id', 'machine_id', 'production_date');

        if ($search) {
            $query->where('production_no', 'LIKE', "%{$search}%");
        }

        return response()->json($query->get());
    }
}
