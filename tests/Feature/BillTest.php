<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillBank;
use App\Models\BillClaim;
use App\Models\BillItem;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserQris;
use App\Services\QrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $response->assertSee('Harga Satuan');
        $response->assertSee('Harga Total');
        $response->assertSee('Panduan Format');
        $response->assertSee('Kapan Harus Memilih Format?');
        $response->assertDontSee('id="tabManualMode"', false);
        $response->assertSee('Konfirmasi & Cek Ulang Tagihan', false);
        $response->assertSee('Peringatan: Periksa Hasil Pindai AI');
        $response->assertSee('AI sangat mungkin melakukan kesalahan');
        $response->assertSee('id="confirmSubmitModal"', false);
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
            'is_primary' => true,
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
        $response->assertSee('Salin Tautan');
        $response->assertSee('Bayar Sekarang');
        $response->assertSee('QRIS Dinamis');
        $response->assertSee('Unduh Card QR');
        $response->assertDontSee('Salin String QR');
        $response->assertSee('AkuOnline IT Services');
        $response->assertSee('Traktir Kopi');
    }

    public function test_participant_can_calculate_selection_with_proportional_fees(): void
    {
        $bill = Bill::factory()->create([
            'delivery_fee' => 10000,
            'service_fee' => 2000,
            'discount' => 2000,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Nasi Goreng',
            'qty' => 2,
            'price' => 20000, // Total 40000
        ]);

        $item2 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Es Jeruk',
            'qty' => 2,
            'price' => 5000, // Total 10000. Total bill items = 50000
        ]);

        // Participant selects 1x Nasi Goreng (20000 out of 50000 = 40%)
        // Net fees: 10000 + 2000 - 2000 = 10000. 40% of 10000 = 4000.
        // Total expected: 20000 + 4000 = 24000
        $response = $this->postJson('/b/'.$bill->slug.'/calculate', [
            'items' => [
                $item1->id => 1,
            ],
            'round_up' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'items_subtotal' => 20000,
            'total_payable' => 24000,
        ]);
    }

    public function test_participant_can_claim_payment(): void
    {
        $bill = Bill::factory()->create();
        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Mie Ayam',
            'qty' => 2,
            'price' => 15000,
        ]);

        $response = $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Budi Santoso',
            'payment_method' => 'qris',
            'items' => [
                $item->id => 1,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);
    }

    public function test_host_can_confirm_and_reject_payment_claims(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $host->id]);
        $item = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 25000]);

        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Andi',
            'amount' => 25000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // Host confirms claim
        $confirmResponse = $this->actingAs($host)->post("/b/{$bill->slug}/claims/{$claim->id}/confirm");
        $confirmResponse->assertRedirect();
        $this->assertEquals('confirmed', $claim->fresh()->status);

        // Host rejects/deletes claim
        $rejectResponse = $this->actingAs($host)->delete("/b/{$bill->slug}/claims/{$claim->id}/reject");
        $rejectResponse->assertRedirect();
        $this->assertDatabaseMissing('bill_claims', ['id' => $claim->id]);
    }

    public function test_duplicate_payer_name_on_same_bill_is_rejected(): void
    {
        $bill = Bill::factory()->create();
        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Kopi Tubruk',
            'qty' => 5,
            'price' => 10000,
        ]);

        // First claim
        $firstResponse = $this->postJson("/b/{$bill->slug}/claim", [
            'payer_name' => 'Ferry S',
            'payment_method' => 'qris',
            'items' => [
                $item->id => 1,
            ],
        ]);
        $firstResponse->assertStatus(200);

        // Duplicate claim with same name (case-insensitive & trimmed) is rejected
        $dupResponse = $this->postJson("/b/{$bill->slug}/claim", [
            'payer_name' => '  ferry s  ',
            'payment_method' => 'qris',
            'items' => [
                $item->id => 1,
            ],
        ]);
        $dupResponse->assertStatus(422);
        $dupResponse->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('sudah terdaftar', $dupResponse->json('message'));
    }

    public function test_host_can_confirm_and_reject_payment_claims_via_ajax(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $host->id]);

        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Doni',
            'amount' => 50000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // AJAX Confirm
        $confirmResponse = $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/{$claim->id}/confirm");
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertJson([
            'success' => true,
            'status' => 'confirmed',
            'bill_summary' => [
                'total_confirmed_paid' => 50000,
            ],
        ]);
        $this->assertEquals('confirmed', $claim->fresh()->status);

        // AJAX Reject
        $rejectResponse = $this->actingAs($host)->deleteJson("/b/{$bill->slug}/claims/{$claim->id}/reject");
        $rejectResponse->assertStatus(200);
        $rejectResponse->assertJson([
            'success' => true,
            'claim_id' => $claim->id,
            'bill_summary' => [
                'total_confirmed_paid' => 0,
            ],
        ]);
        $this->assertDatabaseMissing('bill_claims', ['id' => $claim->id]);
    }

    public function test_bill_claim_provides_detail_array_for_modal_popup(): void
    {
        $bill = Bill::factory()->create([
            'delivery_fee' => 10000,
            'service_fee' => 2000,
            'discount' => 2000,
        ]);

        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Soto Ayam',
            'qty' => 2,
            'price' => 25000,
        ]);

        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Sari',
            'amount' => 35000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $claim->claimItems()->create([
            'bill_item_id' => $item->id,
            'qty' => 1,
        ]);

        $detail = $claim->toDetailArray();

        $this->assertEquals('Sari', $detail['payer_name']);
        $this->assertEquals(25000, $detail['items_subtotal']);
        $this->assertCount(1, $detail['items']);
        $this->assertEquals('Soto Ayam', $detail['items'][0]['name']);
        $this->assertEquals($item->id, $detail['items'][0]['item_id']);
        $this->assertArrayHasKey('proportion_percent', $detail);
        $this->assertArrayHasKey('share_delivery', $detail);
        $this->assertArrayHasKey('share_service', $detail);
        $this->assertArrayHasKey('share_discount', $detail);
    }

    public function test_host_can_batch_confirm_selected_claims(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $host->id]);

        $claim1 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Budi',
            'amount' => 15000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $claim2 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Siti',
            'amount' => 20000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $claim3 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Joko',
            'amount' => 30000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // Host batch confirms claim1 and claim3 only
        $response = $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/batch-confirm", [
            'claim_ids' => [$claim1->id, $claim3->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'confirmed_count' => 2,
            'total_amount' => 45000,
            'bill_summary' => [
                'total_confirmed_paid' => 45000,
            ],
        ]);

        $this->assertEquals('confirmed', $claim1->fresh()->status);
        $this->assertEquals('pending', $claim2->fresh()->status);
        $this->assertEquals('confirmed', $claim3->fresh()->status);
    }

    public function test_non_host_cannot_batch_confirm_claims(): void
    {
        $host = User::factory()->create();
        $otherUser = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $host->id]);

        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Doni',
            'amount' => 10000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($otherUser)->postJson("/b/{$bill->slug}/claims/batch-confirm", [
            'claim_ids' => [$claim->id],
        ]);

        $response->assertStatus(404);
        $this->assertEquals('pending', $claim->fresh()->status);
    }

    public function test_claim_actions_accurately_update_bill_summary_and_settlement_status(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 50000,
            'qty' => 2,
        ]);

        // Total bill is 100,000
        $this->assertEquals(100000, $bill->grand_total);
        $this->assertEquals(0, $bill->total_confirmed_paid);
        $this->assertFalse($bill->isFullySettled());

        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Maya',
            'amount' => 100000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $claim->claimItems()->create([
            'bill_item_id' => $item->id,
            'qty' => 2,
        ]);

        // Confirm claim -> should reach 100% and fully settled
        $response = $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/{$claim->id}/confirm");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'bill_summary' => [
                'grand_total' => 100000,
                'total_confirmed_paid' => 100000,
                'remaining_confirmed_amount' => 0,
                'progress_percentage' => 100,
                'is_fully_settled' => true,
            ],
        ]);

        // Reject/delete the confirmed claim -> should drop back to 0% and not fully settled
        $rejectResponse = $this->actingAs($host)->deleteJson("/b/{$bill->slug}/claims/{$claim->id}/reject");
        $rejectResponse->assertStatus(200);
        $rejectResponse->assertJson([
            'success' => true,
            'bill_summary' => [
                'grand_total' => 100000,
                'total_confirmed_paid' => 0,
                'remaining_confirmed_amount' => 100000,
                'progress_percentage' => 0,
                'is_fully_settled' => false,
            ],
        ]);
    }

    public function test_bill_settled_status_is_false_if_only_partial_items_are_confirmed_or_if_items_remain_unclaimed(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 25000,
            'qty' => 1,
        ]);

        $item2 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 25000,
            'qty' => 1,
        ]);

        // Claim 1: for item 1 only
        $claim1 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Budi',
            'amount' => 25000,
            'payment_method' => 'qris',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        $claim1->claimItems()->create([
            'bill_item_id' => $item1->id,
            'qty' => 1,
        ]);

        // Item 2 is NOT claimed yet
        $this->assertFalse($bill->fresh()->isFullySettled());

        // Claim 2: for item 2 (pending)
        $claim2 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Citra',
            'amount' => 25000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);
        $claim2->claimItems()->create([
            'bill_item_id' => $item2->id,
            'qty' => 1,
        ]);

        // Still false because claim 2 is pending
        $this->assertFalse($bill->fresh()->isFullySettled());

        // Host confirms claim 2 -> Now both items are confirmed -> Fully Settled
        $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/{$claim2->id}/confirm");
        $this->assertTrue($bill->fresh()->isFullySettled());

        // Host rejects claim 2 -> Reverts to NOT fully settled
        $this->actingAs($host)->deleteJson("/b/{$bill->slug}/claims/{$claim2->id}/reject");
        $this->assertFalse($bill->fresh()->isFullySettled());
    }

    public function test_claim_with_custom_amount_on_non_qris_separates_bill_amount_and_tip_amount(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 50000,
            'qty' => 1,
        ]);

        $response = $this->postJson("/b/{$bill->slug}/claim", [
            'payer_name' => 'Doni',
            'payment_method' => 'BCA',
            'actual_amount' => 75000,
            'items' => [
                $item->id => 1,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'amount' => 75000,
            'claim_data' => [
                'payer_name' => 'Doni',
                'amount' => 75000,
                'bill_amount' => 50000,
                'tip_amount' => 25000,
                'has_tip' => true,
            ],
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Doni',
            'amount' => 75000,
            'bill_amount' => 50000,
            'tip_amount' => 25000,
            'status' => 'pending',
        ]);

        $claim = BillClaim::where('bill_id', $bill->id)->first();
        $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/{$claim->id}/confirm");

        $freshBill = $bill->fresh();
        $this->assertEquals(50000, $freshBill->total_confirmed_paid);
        $this->assertEquals(25000, $freshBill->total_confirmed_tips);
        $this->assertEquals(0, $freshBill->remaining_confirmed_amount);
        $this->assertTrue($freshBill->isFullySettled());
    }

    public function test_custom_amount_cannot_be_less_than_exact_bill_share(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 50000,
            'qty' => 1,
        ]);

        $response = $this->postJson("/b/{$bill->slug}/claim", [
            'payer_name' => 'Fani',
            'payment_method' => 'Cash',
            'actual_amount' => 40000, // Less than 50000
            'items' => [
                $item->id => 1,
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertDatabaseMissing('bill_claims', [
            'payer_name' => 'Fani',
        ]);
    }

    public function test_tip_amount_does_not_affect_remaining_bill_of_other_participants(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 50000,
            'qty' => 1,
        ]);

        $item2 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 50000,
            'qty' => 1,
        ]);

        // Participant 1 pays 75,000 (50,000 bill + 25,000 tip)
        $this->postJson("/b/{$bill->slug}/claim", [
            'payer_name' => 'Kawan 1',
            'payment_method' => 'Mandiri',
            'actual_amount' => 75000,
            'items' => [
                $item1->id => 1,
            ],
        ]);

        $claim1 = BillClaim::where('bill_id', $bill->id)->first();
        $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/{$claim1->id}/confirm");

        $freshBill = $bill->fresh();
        // Grand total is 100,000. Confirmed paid for the bill is ONLY 50,000 (not 75,000!)
        $this->assertEquals(100000, $freshBill->grand_total);
        $this->assertEquals(50000, $freshBill->total_confirmed_paid);
        $this->assertEquals(25000, $freshBill->total_confirmed_tips);
        $this->assertEquals(50000, $freshBill->remaining_confirmed_amount);
        $this->assertFalse($freshBill->isFullySettled());
    }

    public function test_bill_page_provides_developer_donation_qris_information(): void
    {
        $qrisService = app(QrisService::class);
        $payload = $qrisService->getDeveloperQrisPayload();

        $this->assertNotEmpty($payload);
        $this->assertTrue($qrisService->isValidQris($payload));

        $merchantInfo = $qrisService->extractMerchantInfo($payload);
        $this->assertEquals('AkuOnline IT Services', $merchantInfo['merchant_name']);
        $this->assertEquals('KOTA TANGERANG', $merchantInfo['merchant_city']);

        $bill = Bill::factory()->create();
        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertDontSee('QRIS DUKUNG DEVELOPER');
        $response->assertSee('AkuOnline IT Services');
        $response->assertSee('postTransactionAlert');
        $response->assertSee('btnOpenCoffeeModalFromAlert');
        $response->assertSee('buyCoffeeModal');
        $response->assertSee('btnDownloadCoffeeCard');
        $response->assertSee('Traktir Kopi Mas Dev');
    }

    public function test_primary_bank_is_prioritized_with_badge_in_bill_page(): void
    {
        $bill = Bill::factory()->create();

        // Create non-primary bank first
        BillBank::factory()->create([
            'bill_id' => $bill->id,
            'bank_name' => 'Bank Mandiri',
            'account_number' => '111222333444',
            'is_primary' => false,
        ]);

        // Create primary bank second
        BillBank::factory()->create([
            'bill_id' => $bill->id,
            'bank_name' => 'BCA Utama',
            'account_number' => '999888777666',
            'is_primary' => true,
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertSee('BCA Utama');
        $response->assertSee('Utama');

        // Verify order in relationship: primary bank must be first
        $orderedBanks = $bill->fresh()->banks;
        $this->assertEquals('BCA Utama', $orderedBanks->first()->bank_name);
        $this->assertTrue((bool) $orderedBanks->first()->is_primary);
    }

    public function test_bill_without_qris_does_not_show_qris_option_in_claim_modal(): void
    {
        $bill = Bill::factory()->create([
            'qris_payload' => null,
            'qris_merchant_name' => null,
        ]);

        BillBank::factory()->create([
            'bill_id' => $bill->id,
            'bank_name' => 'BCA',
            'is_primary' => true,
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertDontSee('<option value="qris"', false);
        $response->assertSee('Transfer BCA');
    }

    public function test_bill_detail_has_item_search_and_collapsible_payment_details(): void
    {
        $bill = Bill::factory()->create();

        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Kopi Tubruk Spesial',
            'qty' => 2,
            'price' => 15000,
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertSee('menuSearchInput');
        $response->assertSee('Cari nama item/pesanan...');
        $response->assertSee('btnTogglePayModalDetails');
        $response->assertSee('Lihat Rincian Item', false);
        $response->assertSee('btnToggleClaimModalDetails');
    }

    public function test_guest_cannot_delete_bill(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->delete('/bills/'.$bill->id);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('bills', ['id' => $bill->id]);
    }

    public function test_user_cannot_delete_another_users_bill(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete('/bills/'.$bill->id);

        $response->assertStatus(404);
        $this->assertDatabaseHas('bills', ['id' => $bill->id]);
    }

    public function test_user_can_delete_own_bill_and_cascades_all_relations(): void
    {
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id, 'title' => 'Makan Bareng Divisi']);

        $item = BillItem::factory()->create(['bill_id' => $bill->id, 'name' => 'Sate Ayam']);
        $bank = BillBank::factory()->create(['bill_id' => $bill->id, 'bank_name' => 'BCA']);
        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Doni',
            'amount' => 20000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->delete('/bills/'.$bill->id);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
        $this->assertDatabaseMissing('bill_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('bill_banks', ['id' => $bank->id]);
        $this->assertDatabaseMissing('bill_claims', ['id' => $claim->id]);
    }

    public function test_user_can_delete_bill_via_json(): void
    {
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id, 'title' => 'Ngopi Santai']);

        $response = $this->actingAs($user)->deleteJson('/bills/'.$bill->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('dashboard'),
        ]);

        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
    }

    public function test_dashboard_displays_delete_bill_button(): void
    {
        $user = User::factory()->create();
        $bill = Bill::factory()->create(['user_id' => $user->id, 'title' => 'Makan Siang Bareng']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('btn-delete-bill');
        $response->assertSee('deleteBill('.$bill->id, false);
    }

    public function test_claim_with_fractional_cents_rounds_up_to_whole_rupiah_and_records_surplus_as_tip(): void
    {
        $bill = Bill::factory()->create([
            'delivery_fee' => 2304.50,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 1000,
            'qty' => 1,
        ]);

        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 9000,
            'qty' => 1,
        ]);

        // Test calculate endpoint
        $calcResponse = $this->postJson('/b/'.$bill->slug.'/calculate', [
            'items' => [$item1->id => 1],
            'round_up' => false,
        ]);

        $calcResponse->assertStatus(200);
        $calcResponse->assertJson([
            'success' => true,
            'exact_payable' => 1230.45,
            'total_payable' => 1231,
            'round_up_extra' => 0.55,
        ]);

        // Submit claim
        $claimResponse = $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Alice',
            'payment_method' => 'qris',
            'round_up' => false,
            'items' => [$item1->id => 1],
        ]);

        $claimResponse->assertStatus(200);
        $claimResponse->assertJson([
            'success' => true,
            'amount' => 1231,
            'claim_data' => [
                'payer_name' => 'Alice',
                'amount' => 1231,
                'bill_amount' => 1230.45,
                'tip_amount' => 0.55,
                'has_tip' => true,
            ],
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Alice',
            'amount' => 1231,
            'bill_amount' => 1230.45,
            'tip_amount' => 0.55,
        ]);
    }

    public function test_claim_with_fractional_cents_and_round_up_to_thousands_records_surplus_as_tip(): void
    {
        $bill = Bill::factory()->create([
            'delivery_fee' => 2304.50,
            'service_fee' => 0,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 1000,
            'qty' => 1,
        ]);

        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'price' => 9000,
            'qty' => 1,
        ]);

        // Test calculate endpoint with round_up = true
        $calcResponse = $this->postJson('/b/'.$bill->slug.'/calculate', [
            'items' => [$item1->id => 1],
            'round_up' => true,
        ]);

        $calcResponse->assertStatus(200);
        $calcResponse->assertJson([
            'success' => true,
            'exact_payable' => 1230.45,
            'total_payable' => 2000,
            'round_up_extra' => 769.55,
        ]);

        // Submit claim with round_up = true
        $claimResponse = $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Bob',
            'payment_method' => 'qris',
            'round_up' => true,
            'items' => [$item1->id => 1],
        ]);

        $claimResponse->assertStatus(200);
        $claimResponse->assertJson([
            'success' => true,
            'amount' => 2000,
            'claim_data' => [
                'payer_name' => 'Bob',
                'amount' => 2000,
                'bill_amount' => 1230.45,
                'tip_amount' => 769.55,
                'has_tip' => true,
            ],
        ]);

        $this->assertDatabaseHas('bill_claims', [
            'bill_id' => $bill->id,
            'payer_name' => 'Bob',
            'amount' => 2000,
            'bill_amount' => 1230.45,
            'tip_amount' => 769.55,
        ]);
    }

    public function test_multi_person_split_with_fees_fully_settles_without_underpayment_shortfall(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 10000,
            'service_fee' => 1000,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Menu 1',
            'price' => 15000,
            'qty' => 1,
        ]);

        $item2 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Menu 2',
            'price' => 15000,
            'qty' => 1,
        ]);

        $item3 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Menu 3',
            'price' => 15000,
            'qty' => 1,
        ]);

        // Grand total: 45,000 + 10,000 + 1,000 = 56,000.
        // Each person's exact share = 15,000 + (1/3 * 11,000) = 18,666.67
        // Ceil payment per person = 18,667 (with 0.33 tip)
        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Person 1',
            'payment_method' => 'qris',
            'items' => [$item1->id => 1],
        ])->assertStatus(200);

        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Person 2',
            'payment_method' => 'qris',
            'items' => [$item2->id => 1],
        ])->assertStatus(200);

        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Person 3',
            'payment_method' => 'qris',
            'items' => [$item3->id => 1],
        ])->assertStatus(200);

        $claims = $bill->claims()->get();
        $this->assertCount(3, $claims);

        // Host batch confirms all 3 claims
        $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/batch-confirm", [
            'claim_ids' => $claims->pluck('id')->toArray(),
        ])->assertStatus(200);

        $freshBill = $bill->fresh();
        // Each person paid 18,667 => total collected = 56,001 (>= 56,000)
        $this->assertEquals(0, $freshBill->remaining_confirmed_amount);
        $this->assertTrue($freshBill->isFullySettled());
    }

    public function test_component_first_two_decimal_rounding_eliminates_discrepancy_for_user_scenario(): void
    {
        $bill = Bill::factory()->create([
            'delivery_fee' => 4.73,
            'service_fee' => 2.82,
            'discount' => 9.49,
        ]);

        $item1 = BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Menu 1',
            'price' => 1000,
            'qty' => 1,
        ]);

        BillItem::factory()->create([
            'bill_id' => $bill->id,
            'name' => 'Menu 2',
            'price' => 9000,
            'qty' => 1,
        ]);

        // Proportion for item1 is 1000 / 10000 = 0.1
        // delivery_fee_share = round(0.1 * 4.73, 2) = 0.47
        // service_fee_share = round(0.1 * 2.82, 2) = 0.28
        // discount_share = round(0.1 * 9.49, 2) = 0.95
        // Component-first net fee = 0.47 + 0.28 - 0.95 = -0.20
        // exact_payable = 1000 - 0.20 = 999.80
        // roundedUpPayable = ceil(999.80) = 1000
        // round_up_extra = 1000 - 999.80 = 0.20 (NOT 0.19!)
        $calcResponse = $this->postJson('/b/'.$bill->slug.'/calculate', [
            'items' => [$item1->id => 1],
            'round_up' => false,
        ]);

        $calcResponse->assertStatus(200);
        $calcResponse->assertJson([
            'success' => true,
            'items_subtotal' => 1000,
            'delivery_fee_share' => 0.47,
            'service_fee_share' => 0.28,
            'discount_share' => 0.95,
            'fee_share' => -0.20,
            'exact_payable' => 999.80,
            'total_payable' => 1000,
            'round_up_extra' => 0.20,
        ]);

        // Submit claim
        $claimResponse = $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Budi User Scenario',
            'payment_method' => 'qris',
            'round_up' => false,
            'items' => [$item1->id => 1],
        ]);

        $claimResponse->assertStatus(200);
        $claimResponse->assertJson([
            'success' => true,
            'amount' => 1000,
            'claim_data' => [
                'payer_name' => 'Budi User Scenario',
                'amount' => 1000,
                'bill_amount' => 999.80,
                'tip_amount' => 0.20,
                'share_delivery' => 0.47,
                'share_service' => 0.28,
                'share_discount' => 0.95,
                'fee_share' => -0.20,
                'exact_payable' => 999.80,
                'has_tip' => true,
            ],
        ]);

        $claim = BillClaim::where('bill_id', $bill->id)->first();
        $this->assertNotNull($claim);
        $this->assertEquals(999.80, (float) $claim->bill_amount);
        $this->assertEquals(0.20, (float) $claim->tip_amount);
        $this->assertEquals(999.80, (float) $claim->exact_payable);

        // Verify the mathematical identity: subtotal + deliv + serv - disc + tip == amount
        $detail = $claim->toDetailArray();
        $balancedSum = $detail['items_subtotal']
            + $detail['share_delivery']
            + $detail['share_service']
            - $detail['share_discount']
            + $detail['tip_amount'];
        $this->assertEquals(1000.00, round($balancedSum, 2));
    }

    public function test_multi_participant_split_penny_remainder_normalizes_to_grand_total_when_fully_settled(): void
    {
        $host = User::factory()->create();
        $bill = Bill::factory()->create([
            'user_id' => $host->id,
            'delivery_fee' => 10000,
            'service_fee' => 1000,
            'discount' => 0,
        ]);

        $item1 = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 15000, 'qty' => 1]);
        $item2 = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 15000, 'qty' => 1]);
        $item3 = BillItem::factory()->create(['bill_id' => $bill->id, 'price' => 15000, 'qty' => 1]);

        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Kawan 1',
            'payment_method' => 'qris',
            'items' => [$item1->id => 1],
        ])->assertStatus(200);

        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Kawan 2',
            'payment_method' => 'qris',
            'items' => [$item2->id => 1],
        ])->assertStatus(200);

        $this->postJson('/b/'.$bill->slug.'/claim', [
            'payer_name' => 'Kawan 3',
            'payment_method' => 'qris',
            'items' => [$item3->id => 1],
        ])->assertStatus(200);

        $claims = $bill->claims()->get();
        $this->actingAs($host)->postJson("/b/{$bill->slug}/claims/batch-confirm", [
            'claim_ids' => $claims->pluck('id')->toArray(),
        ])->assertStatus(200);

        $freshBill = $bill->fresh();
        // Grand total is 56,000.
        // Normalized total confirmed paid must be exactly 56,000 (not 55,999.99)
        $this->assertEquals(56000, $freshBill->grand_total);
        $this->assertEquals(56000, $freshBill->total_confirmed_paid);
        $this->assertEquals(0, $freshBill->remaining_confirmed_amount);
        $this->assertEquals(100.0, $freshBill->progress_percentage);
        $this->assertTrue($freshBill->isFullySettled());

        $summary = $freshBill->getSummaryArray();
        $this->assertEquals(56000, $summary['total_confirmed_paid']);
        $this->assertEquals(0, $summary['remaining_confirmed_amount']);
        $this->assertEquals(100.0, $summary['progress_percentage']);
        $this->assertTrue($summary['is_fully_settled']);
    }

    public function test_payment_history_displays_pembulatan_tip_label_with_conditional_decimals(): void
    {
        $bill = Bill::factory()->create();

        // 1. Claim with decimal tip (0.55)
        $claim1 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Alice 0.55',
            'amount' => 1000,
            'bill_amount' => 999.45,
            'tip_amount' => 0.55,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // 2. Claim with single decimal tip (0.50 -> 0.5)
        $claim2 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Bob 0.5',
            'amount' => 1000,
            'bill_amount' => 999.50,
            'tip_amount' => 0.50,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // 3. Claim with whole integer tip (521)
        $claim3 = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => 'Charlie 521',
            'amount' => 50521,
            'bill_amount' => 50000,
            'tip_amount' => 521,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        // Test model accessors
        $this->assertEquals('Rp 0,55', $claim1->formatted_tip_amount);
        $this->assertEquals('Rp 0,5', $claim2->formatted_tip_amount);
        $this->assertEquals('Rp 521', $claim3->formatted_tip_amount);

        // Test bill page renders the correct label and decimal amounts
        $response = $this->get('/b/'.$bill->slug);
        $response->assertStatus(200);

        // Should see the new label +Pembulatan/Tip
        $response->assertSee('+Pembulatan/Tip Rp 0,55');
        $response->assertSee('+Pembulatan/Tip Rp 0,5');
        $response->assertSee('+Pembulatan/Tip Rp 521');

        // Should NOT see the old rounded-off "+Tip Rp 1"
        $response->assertDontSee('+Tip Rp 1');
    }

    public function test_bill_with_receipt_shows_lihat_nota_asli_button_and_modal(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('nota.jpg');
        $storedPath = $file->store('bills/receipts', 'public');

        $bill = Bill::factory()->create([
            'receipt_image_path' => $storedPath,
            'receipt_image_paths' => [$storedPath],
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertSee('Lihat Nota Asli');
        $response->assertSee('id="receiptModal"', false);
        $response->assertSee('Buka di Tab Baru');
        $response->assertSee('Tutup');
        $response->assertSee(Storage::disk('public')->url($storedPath));
    }

    public function test_bill_without_receipt_hides_lihat_nota_asli_button(): void
    {
        $bill = Bill::factory()->create([
            'receipt_image_path' => null,
            'receipt_image_paths' => null,
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Lihat Nota Asli');
        $response->assertDontSee('id="receiptModal"', false);
    }

    public function test_bill_with_missing_receipt_file_shows_error_in_modal(): void
    {
        Storage::fake('public');

        // Stored path exists in database but file does not exist on disk
        $bill = Bill::factory()->create([
            'receipt_image_path' => 'bills/receipts/missing_file.jpg',
            'receipt_image_paths' => ['bills/receipts/missing_file.jpg'],
        ]);

        $response = $this->get('/b/'.$bill->slug);

        $response->assertStatus(200);
        $response->assertSee('Lihat Nota Asli');
        $response->assertSee('id="receiptModal"', false);
        $response->assertSee('Gambar Nota Tidak Tersedia');
        $response->assertSee('File gambar nota tidak ditemukan atau telah dihapus dari server.');
    }

    public function test_bill_store_saves_multiple_receipt_image_paths(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file1 = UploadedFile::fake()->image('receipt1.jpg');
        $file2 = UploadedFile::fake()->image('receipt2.jpg');

        $response = $this->actingAs($user)->post('/bills', [
            'title' => 'Makan Siang Bareng',
            'qris_choice' => 'none',
            'receipt_images' => [$file1, $file2],
            'items' => [
                [
                    'name' => 'Pizza',
                    'qty' => 1,
                    'price' => 100000,
                ],
            ],
        ]);

        $bill = Bill::where('title', 'Makan Siang Bareng')->first();
        $this->assertNotNull($bill);
        $this->assertNotNull($bill->receipt_image_path);
        $this->assertIsArray($bill->receipt_image_paths);
        $this->assertCount(2, $bill->receipt_image_paths);

        foreach ($bill->receipt_image_paths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $response->assertRedirect(route('bills.show', ['slug' => $bill->slug]));

        // Check show page displays both images
        $showResponse = $this->get('/b/'.$bill->slug);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Lihat Nota Asli');
        $showResponse->assertSee('id="receiptModal"', false);
        foreach ($bill->receipt_image_paths as $path) {
            $showResponse->assertSee(Storage::disk('public')->url($path));
        }

        // Deleting bill also cleans up both receipt files
        $deleteResponse = $this->actingAs($user)->delete('/bills/'.$bill->id);
        $deleteResponse->assertRedirect(route('dashboard'));

        foreach ($bill->receipt_image_paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
