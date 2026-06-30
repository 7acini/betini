<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->withoutVite();

        $this->get('/')
            ->assertOk();

        $this->get('/portal')
            ->assertRedirect('/login');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->withoutVite();

        $this->get('/login')
            ->assertOk();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])
            ->assertRedirect('/portal');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_admin_user_seeder_creates_default_admin(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@betini.local')->first();

        $this->assertNotNull($admin);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('Betini@123', $admin->password));
    }
}
