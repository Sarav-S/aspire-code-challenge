<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_without_credentials()
    {
        $response = $this->json('post', '/api/login', []);

        $response->assertStatus(422)
            ->assertSeeText('The email field is required.')
            ->assertSeeText('The password field is required.');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_without_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'me@sarav.co',
            'password' => 'passwordpassword'
        ]);

        $response->assertStatus(400)
            ->assertSeeText('Incorrect email or password');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'me@sarav.co',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }
}
