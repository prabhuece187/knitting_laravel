<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KnittingProduction;
use App\Models\KnittingProductionDetail;
use App\Models\JobMaster;
use App\Models\KnittingMachine;
use Illuminate\Support\Facades\DB;

class KnittingProductionController extends BaseController
{
    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = KnittingProduction::with([
                'jobMaster',
                'machine',
                'details'
            ])
            ->select('knitting_productions.*')
            ->where('user_id', auth()->id());

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                $q->where('knitting_productions.production_no', 'like', "%{$search}%")
                ->orWhere('knitting_productions.shift', 'like', "%{$search}%")
                ->orWhere('knitting_productions.operator_name', 'like', "%{$search}%")
                ->orWhere('knitting_productions.id', 'like', "%{$search}%");

                $q->orWhereHas('jobMaster', function ($qj) use ($search) {
                    $qj->where('job_card_no', 'like', "%{$search}%");
                });

                $q->orWhereHas('machine', function ($qm) use ($search) {
                    $qm->where('machine_name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderBy('knitting_productions.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }

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

    // public function store(Request $request)
    // {
    //     $input = $request->all();

    //     DB::transaction(function() use ($input, &$data) {

    //         $productionNo = $this->productionCreate()->getData()->pro_no;

    //         $data = KnittingProduction::create([
    //             'production_no'   => $productionNo,
    //             'production_date' => $input['production_date'],
    //             'job_card_id'     => $input['job_card_id'],
    //             'machine_id'      => $input['machine_id'] ?? null,
    //             'shift'           => $input['shift'] ?? null,
    //             'operator_name'   => $input['operator_name'] ?? null,
    //             'remarks'         => $input['remarks'] ?? null,
    //             'user_id'         => auth()->id(),
    //         ]);

    //         foreach ($input['details'] as $detail) {
    //             $detail['knitting_production_id'] = $data->id;
    //             $detail['user_id'] = auth()->id();
    //             KnittingProductionDetail::create($detail);
    //         }
    //     });

    //     return response($data);
    // }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();

            $productionNo = $this->productionCreate()->getData()->pro_no;

            $production = KnittingProduction::create([
                'production_no'   => $productionNo,
                'production_date' => $input['production_date'],
                'job_card_id'     => $input['job_card_id'],
                'machine_id'      => $input['machine_id'] ?? null,
                'shift'           => $input['shift'] ?? null,
                'operator_name'   => $input['operator_name'] ?? null,
                'remarks'         => $input['remarks'] ?? null,
                'user_id'         => auth()->id(),
            ]);

            foreach ($input['details'] ?? [] as $detail) {
                KnittingProductionDetail::create([
                    'knitting_production_id' => $production->id,
                    'user_id'                => auth()->id(),
                    'item_id'                => $detail['item_id'] ?? null,
                    'qty'                    => $detail['qty'] ?? 0,
                    'remarks'                => $detail['remarks'] ?? null,
                    'produced_weight'        => $detail['produced_weight'] ?? 0,
                    'rolls_count'        => $detail['rolls_count'] ?? 0,
                    'dia'        => $detail['dia'] ?? 0,
                    'gsm'        => $detail['gsm'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Production Created Successfully',
                'data'    => $production
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating production',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $production = KnittingProduction::with(['jobMaster', 'machine'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $production['details'] = KnittingProductionDetail::where('knitting_production_id', $id)->get();

        return response($production);
    }

    // public function update(Request $request, $id)
    // {
    //     $input = $request->all();

    //     DB::transaction(function() use ($input, $id, &$production) {

    //         $production = KnittingProduction::where('user_id', auth()->id())
    //             ->findOrFail($id);

    //         $production->update([
    //             'production_date' => $input['production_date'],
    //             'job_card_id'     => $input['job_card_id'],
    //             'machine_id'      => $input['machine_id'] ?? null,
    //             'shift'           => $input['shift'] ?? null,
    //             'operator_name'   => $input['operator_name'] ?? null,
    //             'remarks'         => $input['remarks'] ?? null,
    //             'user_id'         => auth()->id(),
    //         ]);

    //         KnittingProductionDetail::where('knitting_production_id', $id)->delete();

    //         foreach ($input['details'] as $detail) {
    //             $detail['knitting_production_id'] = $production->id;
    //             $detail['user_id'] = auth()->id();
    //             KnittingProductionDetail::create($detail);
    //         }
    //     });

    //     return response($production);
    // }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();

            $production = KnittingProduction::where('user_id', auth()->id())
                ->findOrFail($id);

            $production->update([
                'production_date' => $input['production_date'],
                'job_card_id'     => $input['job_card_id'],
                'machine_id'      => $input['machine_id'] ?? null,
                'shift'           => $input['shift'] ?? null,
                'operator_name'   => $input['operator_name'] ?? null,
                'remarks'         => $input['remarks'] ?? null,
            ]);

            // Delete old details
            KnittingProductionDetail::where('knitting_production_id', $id)->delete();

            // Insert new details
            foreach ($input['details'] ?? [] as $detail) {
                KnittingProductionDetail::create([
                    'knitting_production_id' => $production->id,
                    'user_id'                => auth()->id(),
                    'item_id'                => $detail['item_id'] ?? null,
                    'qty'                    => $detail['qty'] ?? 0,
                    'remarks'                => $detail['remarks'] ?? null,
                    'produced_weight'        => $detail['produced_weight'] ?? 0,
                    'rolls_count'            => $detail['rolls_count'] ?? 0,
                    'dia'                    => $detail['dia'] ?? 0,
                    'gsm'                    => $detail['gsm'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Production Updated Successfully',
                'data'    => $production
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error updating production',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function selectList(Request $request)
    {
        $search = $request->input('q');

        $query = KnittingProduction::with(['jobMaster:id,job_card_no', 'machine:id,machine_name'])
            ->select('id', 'production_no', 'job_card_id', 'machine_id', 'production_date')
            ->where('user_id', auth()->id());

        if ($search) {
            $query->where('production_no', 'LIKE', "%{$search}%");
        }

        return response()->json($query->get());
    }
}