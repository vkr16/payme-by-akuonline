<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillClaim;
use App\Models\BillClaimItem;
use App\Models\BillItem;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserQris;
use App\Services\QrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_payment_methods_page(): void
    {
        $response = $this->get(route('payment_methods.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_payment_methods_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('payment_methods.index'));

        $response->assertStatus(200);
        $response->assertSee('QRIS Statis Saya');
        $response->assertSee('Rekening Bank');
    }

    public function test_user_can_add_bank_account_and_first_bank_becomes_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('payment_methods.banks.store'), [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Fikri Dev',
            'is_default' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_banks', [
            'user_id' => $user->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Fikri Dev',
            'is_default' => true,
        ]);
    }

    public function test_user_can_update_bank_account(): void
    {
        $user = User::factory()->create();
        $bank = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'Mandiri',
            'account_number' => '111222333444',
            'account_holder' => 'Old Name',
        ]);

        $response = $this->actingAs($user)->putJson(route('payment_methods.banks.update', ['id' => $bank->id]), [
            'bank_name' => 'Bank Mandiri Updated',
            'account_number' => '999888777666',
            'account_holder' => 'New Name',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_banks', [
            'id' => $bank->id,
            'bank_name' => 'Bank Mandiri Updated',
            'account_number' => '999888777666',
            'account_holder' => 'New Name',
        ]);
    }

    public function test_user_can_set_default_bank(): void
    {
        $user = User::factory()->create();
        $bank1 = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'BCA',
            'is_default' => true,
        ]);
        $bank2 = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'GoPay',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->postJson(route('payment_methods.banks.set_default', ['id' => $bank2->id]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertFalse($bank1->fresh()->is_default);
        $this->assertTrue($bank2->fresh()->is_default);
    }

    public function test_user_can_delete_bank_and_promote_another_if_was_default(): void
    {
        $user = User::factory()->create();
        $bank1 = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'BCA',
            'is_default' => true,
        ]);
        $bank2 = UserBank::factory()->create([
            'user_id' => $user->id,
            'bank_name' => 'BRI',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('payment_methods.banks.destroy', ['id' => $bank1->id]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_banks', ['id' => $bank1->id]);
        $this->assertTrue($bank2->fresh()->is_default);
    }

    public function test_user_cannot_modify_other_users_bank(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $bankB = UserBank::factory()->create([
            'user_id' => $userB->id,
            'bank_name' => 'BSI',
        ]);

        $response = $this->actingAs($userA)->putJson(route('payment_methods.banks.update', ['id' => $bankB->id]), [
            'bank_name' => 'Hacked',
            'account_number' => '0000',
            'account_holder' => 'Hacker',
        ]);

        $response->assertStatus(404);
    }

    public function test_user_can_store_valid_qris_payload(): void
    {
        $user = User::factory()->create();
        $validPayload = '00020101021126580014ID.CO.QRIS.WWW011893600523000000000102150000000000000005204581253033605802ID5913WARUNG BERKAH6007JAKARTA6304ABCD';

        $response = $this->actingAs($user)->postJson(route('payment_methods.qris.store'), [
            'payload' => $validPayload,
            'merchant_name' => 'WARUNG BERKAH',
            'merchant_city' => 'JAKARTA',
            'is_default' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_qris', [
            'user_id' => $user->id,
            'merchant_name' => 'WARUNG BERKAH',
            'is_default' => true,
        ]);
    }

    public function test_qris_service_extracts_merchant_name_and_city_accurately(): void
    {
        $qrisService = new QrisService;
        // Payload with 93600523000... (contains 60 before tag 60) and Tag 60 value "TANGERANG"
        $payload = '00020101021126580014ID.CO.QRIS.WWW011893600523000000000102150000000000000005204581253033605802ID5910PAYME TEST6009TANGERANG6105151116304ABCD';

        $info = $qrisService->extractMerchantInfo($payload);

        $this->assertEquals('PAYME TEST', $info['merchant_name']);
        $this->assertEquals('TANGERANG', $info['merchant_city']);
    }

    public function test_invalid_qris_payload_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('payment_methods.qris.store'), [
            'payload' => 'INVALID_QRIS_STRING_NOT_EMVCO_COMPLIANT_TOO_SHORT',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_and_delete_qris(): void
    {
        $user = User::factory()->create();
        $qris = UserQris::factory()->create([
            'user_id' => $user->id,
            'merchant_name' => 'Old Merchant',
            'merchant_city' => 'Bandung',
            'is_default' => true,
        ]);

        // Update
        $updateResponse = $this->actingAs($user)->putJson(route('payment_methods.qris.update', ['id' => $qris->id]), [
            'merchant_name' => 'New Merchant Updated',
            'merchant_city' => 'Surabaya',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('user_qris', [
            'id' => $qris->id,
            'merchant_name' => 'New Merchant Updated',
            'merchant_city' => 'Surabaya',
        ]);

        // Delete
        $deleteResponse = $this->actingAs($user)->deleteJson(route('payment_methods.qris.destroy', ['id' => $qris->id]));
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('user_qris', ['id' => $qris->id]);
    }

    public function test_dashboard_displays_settled_lunas_status_when_bill_is_fully_paid(): void
    {
        $user = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $user->id,
            'title' => 'Makan Malam Seafood',
        ]);

        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Kepiting Saus Padang',
            'qty' => 1,
            'price' => 100000,
        ]);

        // Create confirmed claim covering all portions
        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Andi',
            'amount' => 100000,
            'payment_method' => 'qris',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        BillClaimItem::create([
            'bill_claim_id' => $claim->id,
            'bill_item_id' => $item->id,
            'portion' => 1.0,
            'amount' => 100000,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Selesai (Lunas)');
        $response->assertSee('1 Selesai');
    }
}
