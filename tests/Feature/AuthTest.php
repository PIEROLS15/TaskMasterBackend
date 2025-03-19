<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use RefreshDatabase; 

    /** @test */
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Piero Llanos',
            'email' => 'pierodanielllanossanchez@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Registrado correctamente']);

        $this->assertDatabaseHas('users', [
            'email' => 'pierodanielllanossanchez@gmail.com',
        ]);
    }

    /** @test */
    public function it_requires_valid_data_to_register()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['error']);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'pierodanielllanossanchez@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'pierodanielllanossanchez@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Credenciales incorrectas']);
    }

    /** @test */
    public function it_can_logout_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada correctamente']);
    }
}