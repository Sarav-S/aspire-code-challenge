<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'scheduled_date'];

    /**
     * Scope a query to only include pending terms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePending($query)
    {
        $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include paid terms.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePaid($query)
    {
        $query->where('status', 'PAID');
    }

    public static function paidTermsCount($loanId)
    {
        return self::where('loan_id', $loanId)
            ->paid()
            ->count();
    }
}
