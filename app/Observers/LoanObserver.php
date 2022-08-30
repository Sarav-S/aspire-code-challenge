<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Term;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function created(Loan $loan)
    {
        $amount = $loan->amount;
        $term = $loan->term;

        $perTerm = $amount / $term;

        $data = [];
        for ($i = 1; $i <= $term; $i++) {
            array_push(
                $data,
                new Term(
                    [
                        'amount' => $perTerm,
                        'scheduled_date' => Carbon::parse($loan->created_at)->addDays($i * 7)
                    ]
                )
            );
        }

        $loan->term()->saveMany($data);
    }
}
