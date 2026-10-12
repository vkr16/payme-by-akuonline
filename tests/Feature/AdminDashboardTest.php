<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillClaim;
use App\Models\BillItem;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserQris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_default_role_2_and_helper_methods(): void
    {
        $user = User::factory()->create();

        $this->assertSame(User::ROLE_USER, $user->roles);
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isUser());

        $admin = User::factory()->admin()->create();

        $this->assertSame(User::ROLE_ADMIN, $admin->roles);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUser());
    }

    public function test_guests_cannot_access_admin_dashboard_and_are_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_with_role_2_are_forbidden_from_admin_dashboard(): void
    {
        $user = User::factory()->create(['roles' => User::ROLE_USER]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admins_with_role_1_can_access_admin_dashboard_and_see_global_data(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin']);

        // Create some sample global data
        $host = User::factory()->create(['name' => 'Host User']);
        UserQris::factory()->create(['user_id' => $host->id]);
        UserBank::factory()->create(['user_id' => $host->id]);

        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'title' => 'Makan Siang Tim',
        ]);
        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Ayam Goreng',
            'qty' => 2,
            'price' => 25000,
        ]);
        BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Andi',
            'amount' => 50000,
            'bill_amount' => 50000,
            'tip_amount' => 5000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Control Center');
        $response->assertSee('Gross Transaksi (GTV)');
        $response->assertSee('Total Terlunasi');
        $response->assertSee('Total Pengguna');
        $response->assertSee('Total Tagihan');
        $response->assertSee('10 Tagihan Terbaru');
        $response->assertSee('Makan Siang Tim');
        $response->assertSee('10 Klaim Pembayaran Terbaru');
        $response->assertSee('Andi');
        $response->assertSee('10 Pengguna Terdaftar Terbaru');
        $response->assertSee('Host User');
    }

    public function test_admin_nav_link_is_visible_for_admin_and_hidden_for_regular_user(): void
    {
        $user = User::factory()->create(['roles' => User::ROLE_USER]);
        $admin = User::factory()->admin()->create();

        // Regular user should not see Admin in navbar
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('title="Admin Control Center"', false);

        // Admin should see Admin in navbar
        $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('title="Admin Control Center"', false);
    }
}
