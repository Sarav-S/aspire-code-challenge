<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoanStatusRequest;

class LoanController extends Controller
{
    public function update(LoanStatusRequest $request, Loan $loan)
    {
        $status = $request->validated()['status'];
        $loan->is_approved = $status;
        $loan->is_approved_by = $request->user()->id;
        $loan->is_approved_on = Carbon::now();
        $loan->save();

        return response()->json(['message' => 'Loan status changed to '.($status ? 'approved' : 'rejected').' successfully.'], 200);
    }
}
