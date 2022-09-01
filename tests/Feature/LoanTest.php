<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\{Admin, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected $payload = [
        'amount' => 10000,
        'term' => 3,
    ];


    // public function setUp(): void
    // {
    //     parent::setUp();
    //     $this->artisan('db:seed');
    // }

    public function test_loans_api_unauthenticated()
    {
        $user = User::factory()->create();

        $response = $this->json('get', '/api/loans')
            ->assertStatus(401)
            ->assertSeeText('Unauthenticated');
    }

    public function test_loans_api_authenticated()
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        $this->actingAs($user);
        $this->json('get', '/api/loans')
            ->assertStatus(200);
    }

    public function test_loans_create_api_unauthenticated()
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        $this->json('post', '/api/loans', [])
            ->assertStatus(401)
            ->assertSeeText('Unauthenticated.');
    }

    public function test_loans_create_api_validation()
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        $this->actingAs($user)
            ->json('post', '/api/loans', [])
            ->assertStatus(422)
            ->assertSeeText('The amount field is required.')
            ->assertSeeText('The term field is required.');
    }

    public function test_loans_create_api_with_negative_validation()
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        $this->actingAs($user)
            ->json(
                'post', '/api/loans',
                [
                    'amount' => -1,
                    'term' => -1,
                ]
            )
            ->assertStatus(422)
            ->assertSeeText('The amount must be at least 1')
            ->assertSeeText('The term must be at least 1');
    }

    public function test_loans_create_api_with_proper_params()
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->tokens);

        $this->actingAs($user)
            ->json(
                'post', '/api/loans',
                $this->payload
            )
            ->assertStatus(201)
            ->assertSeeText('Loan submitted successfully for admin review');

        $this->assertDatabaseHas('loans', $this->payload);
    }

    public function test_show_loan_api()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $loan = $user->loans()->create($this->payload);

        $this->actingAs($user)
            ->json('get', '/api/loans/1000')
            ->assertStatus(404);

        $this->actingAs($user)
            ->json('get', '/api/loans/'.$loan->id)
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'PENDING');

        $loan->is_approved = 1;
        $loan->is_approved_by = $admin->id;
        $loan->is_approved_on = Carbon::now();
        $loan->save();

        $this->actingAs($user)
            ->json('get', '/api/loans/' . $loan->id)
            ->assertStatus(200)
            ->assertJsonPath('data.is_approved', 1);
    }
}
