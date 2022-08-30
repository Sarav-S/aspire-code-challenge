<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Term;
use App\Http\Requests\TermRequest;

class TermController extends Controller
{
    public function update(TermRequest $request, Loan $loan, $id)
    {
        /**
         * We've to check the following conditions
         *
         * 1. It should be user's loan to update the term
         * 2. Loan should have been approved by admin
         * 3. Loan should be in pending state to pay the term
         */
        if ($loan->user_id !== $request->user()->id) {
            return response()->json(
                ['message' => 'Loan not found'],
                404
            );
        }

        if (!$loan->is_approved) {
            return response()->json(
                ['message' => 'Loan is yet to be approved by admin'],
                400
            );
        }

        if ($loan->status === 'PAID') {
            return response()->json(
                ['message' => 'You can\'t pay for a loan which is already paid'],
                400
            );
        }

        $term = Term::where('loan_id', $loan->id)
            ->pending()
            ->find($id);

        if (!$term) {
            return response()->json(
                ['message' => 'Term not found'],
                404
            );
        }

        $amount = $request->validated()['amount'];
        if ((float) $term->amount !== (float) $amount) {
            return response()->json(
                [
                    'message' => 'Loan amount should exactly be '.$term->amount
                ],
                422
            );
        }

        $term->status = 'PAID';
        $term->paid_on = Carbon::now();
        $term->save();

        return response()->json(
            [
                'message' => 'Term amount paid successfully.'
            ],
            200
        );
    }
}
