<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'term'];

    public function term()
    {
        return $this->hasMany(Term::class);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive($query)
    {
        $query->where('is_approved', 1);
    }

    public static function forIndividual(int $id)
    {
        return self::select(['id', 'amount', 'created_at', 'status'])
            ->where('user_id', $id)
            ->active()
            ->latest()
            ->paginate();
    }

    public static function withTerm(int $id, int $userId)
    {
        return self::select(['id', 'amount', 'created_at', 'status'])->with(
            [
                'term' => function ($query) {
                    $query->select(
                        'id', 'loan_id', 'amount',
                        'scheduled_date', 'status'
                    );
                }
            ]
        )
        ->where('user_id', $userId)
        ->active()
        ->first($id);
    }
}
