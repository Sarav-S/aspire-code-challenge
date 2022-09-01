<?php

namespace Tests\Unit;

use App\Models\{Loan, Term, User};
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_relationship()
    {
        $payload = [
            'amount' => 10000,
            'term' => 3,
        ];

        $user = User::factory()->create();
        $loan = $user->loans()->create(
            [
                'amount' => 10000,
                'term' => 3,
            ]
        );

        $this->assertInstanceOf(Loan::class, $loan);
    }

    public function test_forIndividual()
    {
        $payload = [
            'amount' => 10000,
            'term' => 3,
        ];

        $user = User::factory()->create();
        $loan = $user->loans()->create(
            [
                'amount' => 10000,
                'term' => 3,
            ]
        );

        $loans = Loan::forIndividual($user->id);
        $this->assertInstanceOf(LengthAwarePaginator::class, $loans);
        $this->assertEquals(1, $loans->total());
    }

    public function test_withTerm()
    {
        $payload = [
            'amount' => 10000,
            'term' => 3,
        ];

        $user = User::factory()->create();
        $loan = $user->loans()->create(
            [
                'amount' => 10000,
                'term' => 3,
            ]
        );

        $result = Loan::withTerm($loan->id, $user->id);
        $this->assertInstanceOf(Loan::class, $result);
        $this->assertEquals(1, array_key_exists('terms', $result->toArray()));
    }
}
