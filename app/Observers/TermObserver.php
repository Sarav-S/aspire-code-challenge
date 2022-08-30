<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Term;

class TermObserver
{
    /**
     * Handle the Term "updated" event.
     *
     * @param  \App\Models\Term  $term
     * @return void
     */
    public function updated(Term $term)
    {
        $loan = Loan::find($term->loan_id);
        $paidTermsCount = Term::paidTermsCount($loan->id);

        if ((int) $loan->term === (int) $paidTermsCount) {
            $loan->status = 'PAID';
            $loan->settled_on = Carbon::now();
            $loan->save();
        }
    }
}
