<?php

namespace App\Http\Controllers;

use App\Models\KnittingJobLedger;
use Illuminate\Http\Request;

class KnittingJobLedgerController extends Controller
{
    public function index($jobId)
    {
        $entries = KnittingJobLedger::where('job_id', $jobId)
            ->orderBy('created_at')
            ->get();

        $balance = $entries->sum(function ($e) {
            return in_array($e->type, ['yarn_inward']) ? $e->qty : -$e->qty;
        });

        return response()->json([
            'ledger' => $entries,
            'balance' => $balance
        ]);
    }

    public function addEntry(Request $request)
    {
        $entry = KnittingJobLedger::create($request->all());
        return response()->json(['message' => 'Entry added', 'data' => $entry]);
    }
}
