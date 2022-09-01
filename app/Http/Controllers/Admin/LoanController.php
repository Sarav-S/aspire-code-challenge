<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Loan;
use App\Jobs\{LoanApproved, LoanRejected};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoanStatusRequest;

class LoanController extends Controller
{
    public function update(LoanStatusRequest $request, Loan $loan) : JsonResponse
    {
        $status = $request->validated()['status'];
        $loan->is_approved = $status;
        $loan->is_approved_by = $request->user()->id;
        $loan->is_approved_on = Carbon::now();
        $loan->save();

        if ($loan->is_approved === 1) {
            LoanApproved::dispatch($loan);
        } elseif ($loan->is_approved === -1) {
            LoanRejected::dispatch($loan);
        }

        return response()->json(['message' => 'Loan status changed to '.($status === 1 ? 'approved' : 'rejected').' successfully.'], 200);
    }
}
