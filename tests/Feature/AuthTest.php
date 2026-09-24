<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Buat Akun PayMe');
    }

    public function test_new_host_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Fikri M',
            'email' => 'fikri@akuonline.my.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'fikri@akuonline.my.id',
            'name' => 'Fikri M',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'host@akuonline.my.id',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'host@akuonline.my.id',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'host@akuonline.my.id',
            'password' => bcrypt('secret123'),
        ]);

        $this->post('/login', [
            'email' => 'host@akuonline.my.id',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'inactive@akuonline.my.id',
            'password' => bcrypt('secret123'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'inactive@akuonline.my.id',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_guests_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
