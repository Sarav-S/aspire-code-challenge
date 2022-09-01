<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_term_api_unauthenticated()
    {
        $this->json('put', '/api/loans/1/term/1')
            ->assertStatus(401);
    }

    public function test_term_api_validation()
    {
        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/1', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.amount.0', 'The amount field is required.');

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/1', [
                'amount' => -1
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.amount.0', 'The amount must be at least 1.');
    }

    public function test_term_api_with_other_user_loan_id()
    {
        $tempUser = User::factory()->create();
        $tempLoan = $tempUser->loans()->create($this->payload);

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$tempLoan->id.'/term/1', [
                'amount' => 3333.33
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'You can pay term only for the loans owned by you');
    }

    public function test_term_api_with_pending_loan()
    {
        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/1', [
                'amount' => 3333.33
            ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Loan is yet to be approved by admin');
    }

    public function test_term_api_with_rejected_loan()
    {
        $this->loan->is_approved = -1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->save();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/1', [
                'amount' => 3333.33
            ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Loan has been rejected by admin');
    }

    public function test_term_api_with_already_settled_loan()
    {
        $this->loan->is_approved = 1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->status = "PAID";
        $this->loan->save();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/10', [
                'amount' => 3333.33
            ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'You can\'t pay for a loan which is already paid');
    }

    public function test_term_api_with_correct_loan_id_and_incorrect_loan_id()
    {
        $this->loan->is_approved = 1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->save();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/1000', [
                'amount' => 3333.33
            ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Term not found');
    }

    public function test_term_api_with_incorrect_amount()
    {
        $this->loan->is_approved = 1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->save();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/'.$this->loan->terms()->first()->id, [
                'amount' => 400
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Loan amount should exactly be 3333.33');
    }

    public function test_term_api_with_correct_amount()
    {
        $this->loan->is_approved = 1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->save();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/'.$this->loan->terms()->first()->id, [
                'amount' => 3333.33
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Term amount paid successfully.');
    }

    public function test_loan_status_after_term_payment()
    {
        $this->loan->is_approved = 1;
        $this->loan->is_approved_by = $this->admin->id;
        $this->loan->is_approved_on = Carbon::now();
        $this->loan->save();

        $terms = $this->loan->terms()->get();

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/'.$terms[0]['id'], [
                'amount' => 3333.33
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Term amount paid successfully.');

        $this->actingAs($this->user)
            ->json('get', '/api/loans/'.$this->loan->id)
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'PENDING');

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/'.$terms[1]['id'], [
                'amount' => 3333.33
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Term amount paid successfully.');

        $this->actingAs($this->user)
            ->json('get', '/api/loans/'.$this->loan->id)
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'PENDING');

        $this->actingAs($this->user)
            ->json('put', '/api/loans/'.$this->loan->id.'/term/'.$terms[2]['id'], [
                'amount' => 3333.33
            ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Term amount paid successfully.');

        $this->actingAs($this->user)
            ->json('get', '/api/loans/'.$this->loan->id)
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'PAID');
    }
}
