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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_updated_cards(): void
    {
        $user = User::factory()->create();

        // Create QRIS & Bank
        UserQris::factory()->create(['user_id' => $user->id]);
        UserBank::factory()->create(['user_id' => $user->id]);

        // Create a bill with confirmed claims including tip
        $bill = Bill::factory()->create(['user_id' => $user->id]);
        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Kopi Tubruk',
            'qty' => 1,
            'price' => 50000,
        ]);

        BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Budi',
            'amount' => 52000,
            'bill_amount' => 50000,
            'tip_amount' => 2000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Tagihan');
        $response->assertSee('Total Transaksi');
        $response->assertSee('Rp 50.000');
        $response->assertSee('Tip diperoleh:');
        $response->assertSee('Rp 2.000');
        $response->assertSee('QRIS & Rekening Terhubung', false);
    }
}
