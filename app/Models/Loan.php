<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'term'];

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public static function forIndividual(int $id)
    {
        return self::select(['id', 'amount', 'created_at', 'status'])
            ->where('user_id', $id)
            ->latest()
            ->paginate();
    }

    public static function withTerm(int $id, int $userId)
    {
        return self::select(['id', 'amount', 'created_at', 'status', 'is_approved'])->with(
            [
                'terms' => function ($query) {
                    $query->select(
                        'id', 'loan_id', 'amount',
                        'scheduled_date', 'status',
                    );
                }
            ]
        )
        ->where('user_id', $userId)
        ->find($id);
    }
}
