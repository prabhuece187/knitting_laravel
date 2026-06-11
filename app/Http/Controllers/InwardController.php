<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Inward;
use App\Models\InwardDetail;
use App\Models\YarnType;
use Illuminate\Support\Facades\DB;

class InwardController extends BaseController
{

    public function index(Request $request)
    {
        $page   = (int) $request->get('page', 1);
        $limit  = (int) $request->get('limit', 10);
        $search = $request->get('search');

        $query = Inward::with(['customer','mill'])
            ->select('inwards.*')
            ->where('inwards.user_id', auth()->id()); // ✅ USER BASED

        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('inwards.inward_no', 'like', "%{$search}%")
                ->orWhere('inwards.vehicle_no', 'like', "%{$search}%")
                ->orWhere('inwards.lot_no', 'like', "%{$search}%")
                ->orWhere('inwards.id', 'like', "%{$search}%")

                ->orWhereHas('customer', function ($qc) use ($search) {
                    $qc->where('customer_name', 'like', "%{$search}%");
                })

                ->orWhereHas('mill', function ($qm) use ($search) {
                    $qm->where('mill_name', 'like', "%{$search}%");
                });

            });

        }

        $query->orderBy('inwards.id', 'desc');

        return response()->json(
            $this->paginate($query, $page, $limit)
        );
    }


    public function InwardCreate(Request $request)
    {
        $data = Inward::where('user_id', auth()->id())
            ->select('inward_invoice_no')
            ->orderBy('inward_invoice_no','DESC')
            ->first();

        return isset($data) ? ($data->inward_invoice_no + 1) : 1;
    }

    // public function store(Request $request)
    // {
    //     $input = $request->all();
    //     $input['user_id'] = auth()->id(); 

    //     $inward = Inward::create($input);

    //     foreach ($request->inward_details ?? [] as $detail) {
    //         InwardDetail::create([
    //             'inward_id'    => $inward->id,
    //             'user_id'      => auth()->id(),
    //             'item_id'      => $detail['item_id'],
    //             'yarn_type_id' => $detail['yarn_type_id'],
    //             'shade'        => $detail['shade'] ?? null,
    //             'bag_no'       => $detail['bag_no'] ?? null,
    //             'gross_weight' => $detail['gross_weight'] ?? 0,
    //             'tare_weight'  => $detail['tare_weight'] ?? 0,
    //             'net_weight'   => $detail['net_weight'] ?? 0,
    //             'uom'          => $detail['uom'] ?? null,
    //             'remarks'      => $detail['remarks'] ?? null,
    //             'job_card_id'  => null
    //         ]);
    //     }

    //     return response()->json($inward);
    // }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();
            $input['user_id'] = auth()->id();

            $inward = Inward::create($input);

            foreach ($request->inward_details ?? [] as $detail) {
                InwardDetail::create([
                    'inward_id'    => $inward->id,
                    'user_id'      => auth()->id(),
                    'item_id'      => $detail['item_id'],
                    'yarn_type_id' => $detail['yarn_type_id'],
                    'yarn_colour'  => $detail['yarn_colour'] ?? null,
                    'bag_no'       => $detail['bag_no'] ?? null,
                    'yarn_dia'     => $detail['yarn_dia'] ?? null,
                    'yarn_gsm'     => $detail['yarn_gsm'] ?? null,
                    'yarn_gauge'   => $detail['yarn_gauge'] ?? null,
                    'gross_weight' => $detail['gross_weight'] ?? 0,
                    'tare_weight'  => $detail['tare_weight'] ?? 0,
                    'inward_weight'=> $detail['gross_weight'] - $detail['tare_weight'],
                    'net_weight'   => $detail['net_weight'] ?? 0,
                    'uom'          => $detail['uom'] ?? null,
                    'remarks'      => $detail['remarks'] ?? null,
                    'job_card_id'  => null
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inward Created Successfully',
                'data' => $inward
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error Creating Inward',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function InwardEdit($id)
    {
        $inward = Inward::with('customer','mill')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $inward['Items'] = InwardDetail::with('item','jobMaster','yarnType')
            ->where('inward_id',$id)
            ->where('user_id',auth()->id())
            ->get();

        return response($inward);
    }

    // public function InwardUpdate(Request $request, $id)
    // {
    //     $input = $request->all();
    //     $inward = Inward::where('user_id', auth()->id())->findOrFail($id);

    //     $inward->update($input);

    //     $existingIds = $inward->inward_details()->pluck('id')->toArray();
    //     $incomingIds = collect($request->inward_details)
    //                     ->pluck('id')
    //                     ->filter()
    //                     ->toArray();
    //     $deleteIds = array_diff($existingIds, $incomingIds);

    //     InwardDetail::whereIn('id', $deleteIds)->delete();

    //     foreach ($request->inward_details ?? [] as $detail) {

    //         if (!empty($detail['id'])) {
    //             InwardDetail::where('id', $detail['id'])->update([
    //                 'item_id'      => $detail['item_id'],
    //                 'yarn_type_id' => $detail['yarn_type_id'],
    //                 'shade'        => $detail['shade'] ?? null,
    //                 'bag_no'       => $detail['bag_no'] ?? null,
    //                 'gross_weight' => $detail['gross_weight'] ?? 0,
    //                 'tare_weight'  => $detail['tare_weight'] ?? 0,
    //                 'net_weight'   => $detail['net_weight'] ?? 0,
    //                 'uom'          => $detail['uom'] ?? null,
    //                 'remarks'      => $detail['remarks'] ?? null,
    //             ]);
    //         } else {
    //             InwardDetail::create([
    //                 'inward_id'    => $inward->id,
    //                 'user_id'      => auth()->id(),
    //                 'item_id'      => $detail['item_id'],
    //                 'yarn_type_id' => $detail['yarn_type_id'],
    //                 'shade'        => $detail['shade'] ?? null,
    //                 'bag_no'       => $detail['bag_no'] ?? null,
    //                 'gross_weight' => $detail['gross_weight'] ?? 0,
    //                 'tare_weight'  => $detail['tare_weight'] ?? 0,
    //                 'net_weight'   => $detail['net_weight'] ?? 0,
    //                 'uom'          => $detail['uom'] ?? null,
    //                 'remarks'      => $detail['remarks'] ?? null,
    //                 'job_card_id'  => null
    //             ]);
    //         }
    //     }
    //     return response()->json($inward);
    // }

    // public function InwardUpdate(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $input = $request->all();

    //         $inward = Inward::where('user_id', auth()->id())->findOrFail($id);

    //         $inward->update($input);

    //         $existingIds = $inward->inward_details()->pluck('id')->toArray();

    //         $incomingIds = collect($request->inward_details)
    //             ->pluck('id')
    //             ->filter()
    //             ->toArray();

    //         $deleteIds = array_diff($existingIds, $incomingIds);
    //         InwardDetail::whereIn('id', $deleteIds)->delete();

    //         foreach ($request->inward_details ?? [] as $detail) {

    //             if (!empty($detail['id'])) {
    //                 InwardDetail::where('id', $detail['id'])->update([
    //                     'item_id'      => $detail['item_id'],
    //                     'yarn_type_id' => $detail['yarn_type_id'],
    //                     'shade'        => $detail['shade'] ?? null,
    //                     'bag_no'       => $detail['bag_no'] ?? null,
    //                     'gross_weight' => $detail['gross_weight'] ?? 0,
    //                     'tare_weight'  => $detail['tare_weight'] ?? 0,
    //                     'net_weight'   => $detail['net_weight'] ?? 0,
    //                     'uom'          => $detail['uom'] ?? null,
    //                     'remarks'      => $detail['remarks'] ?? null,
    //                 ]);
    //             } else {
    //                 InwardDetail::create([
    //                     'inward_id'    => $inward->id,
    //                     'user_id'      => auth()->id(),
    //                     'item_id'      => $detail['item_id'],
    //                     'yarn_type_id' => $detail['yarn_type_id'],
    //                     'shade'        => $detail['shade'] ?? null,
    //                     'bag_no'       => $detail['bag_no'] ?? null,
    //                     'gross_weight' => $detail['gross_weight'] ?? 0,
    //                     'tare_weight'  => $detail['tare_weight'] ?? 0,
    //                     'net_weight'   => $detail['net_weight'] ?? 0,
    //                     'uom'          => $detail['uom'] ?? null,
    //                     'remarks'      => $detail['remarks'] ?? null,
    //                     'job_card_id'  => null
    //                 ]);
    //             }
    //         }
    //         DB::commit();
    //         return response()->json([
    //             'message' => 'Inward Updated Successfully',
    //             'data' => $inward
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error updating inward',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function InwardUpdate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();
            $inward = Inward::where('user_id', auth()->id())->findOrFail($id);

            $inward->update($input);

            $existingIds = $inward->inward_details()->pluck('id')->toArray();

            $incomingIds = collect($request->inward_details)
                ->pluck('id')
                ->filter()
                ->toArray();

            // ✅ DELETE removed rows
            $deleteIds = array_diff($existingIds, $incomingIds);
            InwardDetail::whereIn('id', $deleteIds)->delete();

            foreach ($request->inward_details ?? [] as $detail) {
                $gross = $detail['gross_weight'] ?? 0;
                $tare  = $detail['tare_weight'] ?? 0;
                $net   = $gross - $tare;

                if (!empty($detail['id'])) {
                    // ✅ UPDATE
                    InwardDetail::where('id', $detail['id'])->update([
                        'item_id'      => $detail['item_id'],
                        'yarn_type_id' => $detail['yarn_type_id'],
                        // ✅ FIXED FIELD
                        'yarn_colour'  => $detail['yarn_colour'] ?? '',
                        'yarn_gauge'   => $detail['yarn_gauge'] ?? '',
                        'yarn_dia'     => $detail['yarn_dia'] ?? 0,
                        'yarn_gsm'     => $detail['yarn_gsm'] ?? 0,
                        'bag_no'       => $detail['bag_no'] ?? null,
                        'gross_weight' => $gross,
                        'tare_weight'  => $tare,
                        // ✅ ALWAYS CALCULATE
                        'net_weight'   => $net,
                        // ❗ DO NOT CHANGE inward_weight if already exists
                        // keep original
                        'uom'          => $detail['uom'] ?? 'kg',
                        'remarks'      => $detail['remarks'] ?? null,
                        'job_card_id'  => $detail['job_card_id'] ?? null,
                    ]);

                } else {
                    // ✅ INSERT
                    InwardDetail::create([
                        'inward_id'    => $inward->id,
                        'user_id'      => auth()->id(),
                        'item_id'      => $detail['item_id'],
                        'yarn_type_id' => $detail['yarn_type_id'],
                        'yarn_colour'  => $detail['yarn_colour'] ?? '',
                        'yarn_gauge'   => $detail['yarn_gauge'] ?? '',
                        'yarn_dia'     => $detail['yarn_dia'] ?? 0,
                        'yarn_gsm'     => $detail['yarn_gsm'] ?? 0,
                        'bag_no'       => $detail['bag_no'] ?? null,
                        'gross_weight' => $gross,
                        'tare_weight'  => $tare,
                        // ✅ MAIN LOGIC
                        'net_weight'   => $net,
                        'inward_weight'=> $net, // only for NEW rows
                        'uom'          => $detail['uom'] ?? 'kg',
                        'remarks'      => $detail['remarks'] ?? null,
                        'job_card_id'  => $detail['job_card_id'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Inward Updated Successfully',
                'data' => $inward
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error updating inward',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function linkJobCard(Request $request, $id)
    // {
    //     $detail = InwardDetail::where('user_id',auth()->id())->findOrFail($id);
    //     if ($detail->job_card_id) {
    //         return response()->json([
    //             'message' => 'Job Card already linked'
    //         ],403);
    //     }

    //     $detail->update([
    //         'job_card_id' => $request->job_card_id
    //     ]);

    //     return response()->json($detail);
    // }

    public function linkJobCard(Request $request, $id)
    {
        $detail = InwardDetail::where('user_id', auth()->id())->findOrFail($id);

        if ($detail->job_card_id) {
            return response()->json([
                'message' => 'Job Card Already Linked'
            ], 403);
        }

        $detail->update([
            'job_card_id' => $request->job_card_id
        ]);

        return response()->json([
            'message' => 'Job Card Linked Successfully',
            'data' => $detail
        ], 200);
    }

    public function InwardSelectList(Request $request)
    {
        $search = $request->input('q');
        $query = Inward::with([
                'customer:id,customer_name',
                'mill:id,mill_name'
            ])
            ->select(
                'id',
                'inward_no',
                'customer_id',
                'mill_id',
                'total_weight',
                'remarks'
            )
            ->where('user_id',auth()->id()); // ✅ USER BASED

        if ($search) {
            $query->where('inward_no','like',"%{$search}%");
        }

        return response()->json($query->get());
    }

}