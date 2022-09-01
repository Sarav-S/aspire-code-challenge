<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Term;
use App\Http\Requests\TermRequest;
use Illuminate\Http\JsonResponse;

class TermController extends Controller
{
    public function update(TermRequest $request, Loan $loan, $id) : JsonResponse
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json(
                ['message' => 'You can pay term only for the loans owned by you'],
                404
            );
        }

        if ($loan->is_approved === 0) {
            return response()->json(
                ['message' => 'Loan is yet to be approved by admin'],
                400
            );
        }

        if ($loan->is_approved === -1) {
            return response()->json(
                ['message' => 'Loan has been rejected by admin'],
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
