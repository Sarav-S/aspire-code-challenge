<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminLoginTest extends TestCase
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
        $response = $this->postJson('/admin/api/login', []);

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
        $response = $this->postJson('/admin/api/login', [
            'email' => 'admin@example.com',
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
        $response = $this->postJson('/admin/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }
}
