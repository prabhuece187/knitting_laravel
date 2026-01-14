<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KnittingReportController extends Controller
{
    /**
     * Job Ledger Report
     */
    public function jobLedger(Request $request)
    {
        $jobCardId = $request->job_card_id;

        if (!$jobCardId) {
            return response()->json([
                'message' => 'Job Card ID is required'
            ], 422);
        }

        $yarnIssued = DB::table('inward_details')
            ->where('job_card_id', $jobCardId)
            ->sum('inward_weight');

        $produced = DB::table('knitting_production_details')
            ->join(
                'knitting_productions',
                'knitting_productions.id',
                '=',
                'knitting_production_details.knitting_production_id'
            )
            ->where('knitting_productions.job_card_id', $jobCardId)
            ->sum('produced_weight');

        $returned = DB::table('knitting_production_returns')
            ->where('job_card_id', $jobCardId)
            ->sum('return_weight');

        $reworked = DB::table('knitting_reworks')
            ->where('job_card_id', $jobCardId)
            ->sum('rework_weight');

        $outward = DB::table('outward_details')
            ->where('job_card_id', $jobCardId)
            ->sum('fabric_weight');

        $returnBalance = $returned - $reworked;
        $wipBalance    = ($produced + $reworked) - $outward;
        $wastage       = $produced - ($outward + $returnBalance);

        return response()->json([
            'job_card_id'        => $jobCardId,
            'yarn_issued_kg'     => $yarnIssued,
            'fabric_produced_kg'=> $produced,
            'fabric_returned_kg'=> $returned,
            'fabric_reworked_kg'=> $reworked,
            'fabric_outward_kg' => $outward,
            'wip_balance_kg'    => $wipBalance,
            'wastage_kg'        => $wastage,
        ]);
    }

    /**
     * Wastage Report
     */
    public function wastageReport()
    {
        return DB::table('knitting_productions')
            ->leftJoin(
                'knitting_production_details',
                'knitting_productions.id',
                '=',
                'knitting_production_details.knitting_production_id'
            )
            ->leftJoin(
                'outward_details',
                'outward_details.job_card_id',
                '=',
                'knitting_productions.job_card_id'
            )
            ->leftJoin(
                'knitting_reworks',
                'knitting_reworks.job_card_id',
                '=',
                'knitting_productions.job_card_id'
            )
            ->select(
                'knitting_productions.job_card_id',
                DB::raw('COALESCE(SUM(knitting_production_details.produced_weight),0) as produced'),
                DB::raw('COALESCE(SUM(outward_details.fabric_weight),0) as outward'),
                DB::raw('COALESCE(SUM(knitting_reworks.rework_weight),0) as reworked'),
                DB::raw('
                    COALESCE(SUM(knitting_production_details.produced_weight),0)
                    - (
                        COALESCE(SUM(outward_details.fabric_weight),0)
                        + COALESCE(SUM(knitting_reworks.rework_weight),0)
                    ) as wastage
                ')
            )
            ->groupBy('knitting_productions.job_card_id')
            ->get();
    }

}
