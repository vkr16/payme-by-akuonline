<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserQris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_bill_creation_page(): void
    {
        $response = $this->get('/bills/create');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_bill_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bills/create');

        $response->assertStatus(200);
        $response->assertSee('Informasi Patungan');
        $response->assertSee('Scan Struk (AI)');
    }

    public function test_user_can_create_bill_with_manual_items_and_redirect_to_show(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/bills', [
            'title' => 'Makan Siang Sederhana',
            'qris_choice' => 'none',
            'delivery_fee' => 10000,
            'service_fee' => 2000,
            'discount' => 5000,
            'items' => [
                [
                    'name' => 'Nasi Rendang',
                    'qty' => 2,
                    'price' => 25000,
                ],
                [
                    'name' => 'Es Teh Manis',
                    'qty' => 2,
                    'price' => 5000,
                ],
            ],
        ]);

        $this->assertDatabaseHas('bills', [
            'user_id' => $user->id,
            'title' => 'Makan Siang Sederhana',
            'delivery_fee' => 10000,
            'service_fee' => 2000,
            'discount' => 5000,
        ]);

        $bill = Bill::where('title', 'Makan Siang Sederhana')->first();
        $this->assertNotNull($bill);
        $this->assertCount(2, $bill->items);

        $response->assertRedirect(route('bills.show', ['slug' => $bill->slug]));
    }

    public function test_creating_bill_can_save_new_qris_and_bank_to_user_profile(): void
    {
        $user = User::factory()->create();

        $sampleQris = '00020101021126580014ID.LINKAJA.WWW01189360000000000000000208123456785204549953033605802ID5914WARUNG MAKAN X6007JAKARTA6304A1B2';

        $response = $this->actingAs($user)->post('/bills', [
            'title' => 'Patungan Kopi',
            'qris_choice' => 'new',
            'new_qris_payload' => $sampleQris,
            'save_new_qris' => 1,
            'enable_bank' => 1,
            'new_banks' => [
                [
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_holder' => 'Fikri M',
                    'save_to_profile' => 1,
                ],
            ],
            'items' => [
                [
                    'name' => 'Kopi Latte',
                    'qty' => 1,
                    'price' => 30000,
                ],
            ],
        ]);

        $this->assertDatabaseHas('user_qris', [
            'user_id' => $user->id,
            'merchant_name' => 'WARUNG MAKAN X',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('user_banks', [
            'user_id' => $user->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Fikri M',
            'is_default' => true,
        ]);

        $bill = Bill::where('title', 'Patungan Kopi')->first();
        $this->assertNotNull($bill);
        $this->assertDatabaseHas('bill_banks', [
            'bill_id' => $bill->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
        ]);
        $response->assertRedirect(route('bills.show', ['slug' => $bill->slug]));
    }

    public function test_creating_bill_can_use_saved_qris_and_banks(): void
    {
        $user = User::factory()->create();

        $savedQris = UserQris::factory()->create([
            'user_id' => $user->id,
            'merchant_name' => 'RESTO SAYA',
            'merchant_city' => 'JAKARTA',
            'payload' => '00020101021126580014ID.LINKAJA.WWW01189360000000000000000208123456785204549953033605802ID5910RESTO SAYA6007JAKARTA6304A1B2',
            'is_default' => true,
        ]);

        $savedBank = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'GoPay',
            'account_number' => '081299998888',
            'account_holder' => 'Fikri Host',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->post('/bills', [
            'title' => 'Makan Bareng Divisi',
            'qris_choice' => 'saved',
            'saved_qris_id' => $savedQris->id,
            'enable_bank' => 1,
            'selected_bank_ids' => [$savedBank->id],
            'items' => [
                [
                    'name' => 'Ayam Bakar',
                    'qty' => 1,
                    'price' => 35000,
                ],
            ],
        ]);

        $bill = Bill::where('title', 'Makan Bareng Divisi')->first();
        $this->assertNotNull($bill);
        $this->assertEquals('RESTO SAYA', $bill->qris_merchant_name);
        $this->assertDatabaseHas('bill_banks', [
            'bill_id' => $bill->id,
            'bank_name' => 'GoPay',
            'account_number' => '081299998888',
        ]);
        $response->assertRedirect(route('bills.show', ['slug' => $bill->slug]));
    }

    public function test_public_can_view_bill_detail(): void
    {
        $bill = Bill::factory()->create([
            'title' => 'Acara Ultah Kantor',
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertSee('Acara Ultah Kantor');
        $response->assertSee('Salin Tautan Patungan');
    }
}
