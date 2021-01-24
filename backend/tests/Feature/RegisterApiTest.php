<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @test
     */
    public function should_create_new_user_and_return()
    {
        $data = [
          'name' => 'new User',
          'email' => 'newuser@example.com',
          'password' => 'passw0rd',
          'password_confirmation' => 'passw0rd',
        ];

        $response = $this->json('POST', route('register'), $data);

        $user = User::first();
        $this->assertEquals($data['name'], $user->name);

        $response->assertStatus(201)->assertJson(['name' => $user->name]);
    }
}
