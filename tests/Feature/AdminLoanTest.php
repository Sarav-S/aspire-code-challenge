<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use App\Jobs\{LoanApproved, LoanRejected};

class AdminLoanTest extends TestCase
{
    protected $payload = [
        'amount' => 10000,
        'term' => 3,
    ];

    protected $user;

    protected $admin;

    protected $loan;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = Admin::factory()->create();
        $this->loan = $this->user->loans()->create($this->payload);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_loan_approval_api_without_token()
    {
        $this->json('post', '/admin/api/loans/10', [])
            ->assertStatus(401);
    }

    public function test_loan_approval_api_with_token()
    {
        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/'.$this->loan->id, [])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', 'The status field is required.');

        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/'.$this->loan->id, [
                'status' => true,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', 'The status must be a number.');

        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/'.$this->loan->id, [
                'status' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', 'The selected status is invalid.');
    }

    public function test_loan_approval_api_with_incorrect_id()
    {
        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/100', [
                'status' => 1,
            ])
            ->assertStatus(404);
    }

    public function test_loan_approval_api_with_correct_id()
    {
        Bus::fake();

        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/' . $this->loan->id, [
                'status' => 1,
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Loan status changed to approved successfully.');

        Bus::assertDispatched(LoanApproved::class);

        $this->actingAs($this->admin)
            ->json('post', '/admin/api/loans/' . $this->loan->id, [
                'status' => -1,
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Loan status changed to rejected successfully.');
        Bus::assertDispatched(LoanRejected::class);
    }
}
