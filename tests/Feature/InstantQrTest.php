<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserQris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstantQrTest extends TestCase
{
    use RefreshDatabase;

    protected string $sampleQrisPayload = '00020101021126610014COM.GO-JEK.WWW01189360091431618763450210G1618763450303UMI51440014ID.CO.QRIS.WWW0215ID10253850061230303UMI5204729953033605802ID5921AkuOnline IT Services6014KOTA TANGERANG61051514762070703A016304D59A';

    public function test_guest_cannot_access_instant_qr_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('instant_qr.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_qris_sees_onboarding_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('instant_qr.index'));

        $response->assertStatus(200);
        $response->assertSee('Unggah QRIS Pertamamu');
        $response->assertSee('formFirstQris');
    }

    public function test_authenticated_user_with_qris_sees_instant_qr_generator(): void
    {
        $user = User::factory()->create();
        $qris = UserQris::create([
            'user_id' => $user->id,
            'merchant_name' => 'Kedai Kopi Fikri',
            'merchant_city' => 'Jakarta Selatan',
            'payload' => $this->sampleQrisPayload,
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->get(route('instant_qr.index'));

        $response->assertStatus(200);
        $response->assertSee('Kedai Kopi Fikri');
        $response->assertSee('Nominal Pembayaran');
        $response->assertSee('Tunjukkan ke Pembayar');
        $response->assertSee('Unduh Card (PNG)');
    }

    public function test_generate_dynamic_qris_endpoint_returns_valid_payload(): void
    {
        $user = User::factory()->create();
        $qris = UserQris::create([
            'user_id' => $user->id,
            'merchant_name' => 'Kedai Kopi Fikri',
            'merchant_city' => 'Jakarta Selatan',
            'payload' => $this->sampleQrisPayload,
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('instant_qr.generate'), [
            'qris_id' => $qris->id,
            'amount' => 45000,
            'note' => 'Ganti Kopi Siang',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'merchant_name' => 'Kedai Kopi Fikri',
            'amount' => 45000,
            'formatted_amount' => 'Rp 45.000',
            'note' => 'Ganti Kopi Siang',
        ]);

        $dynamicPayload = $response->json('dynamic_payload');
        $this->assertNotEmpty($dynamicPayload);
        $this->assertStringContainsString('540545000', $dynamicPayload); // Tag 54 with amount 45000
        $this->assertStringContainsString('010212', $dynamicPayload); // Tag 01 Dynamic QRIS indicator
    }

    public function test_generate_dynamic_qris_fails_if_qris_belongs_to_another_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $qrisB = UserQris::create([
            'user_id' => $userB->id,
            'merchant_name' => 'Toko User B',
            'merchant_city' => 'Bandung',
            'payload' => $this->sampleQrisPayload,
            'is_default' => true,
        ]);

        $response = $this->actingAs($userA)->postJson(route('instant_qr.generate'), [
            'qris_id' => $qrisB->id,
            'amount' => 50000,
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_generate_dynamic_qris_validates_amount_must_be_positive(): void
    {
        $user = User::factory()->create();
        $qris = UserQris::create([
            'user_id' => $user->id,
            'merchant_name' => 'Kedai Kopi Fikri',
            'merchant_city' => 'Jakarta Selatan',
            'payload' => $this->sampleQrisPayload,
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('instant_qr.generate'), [
            'qris_id' => $qris->id,
            'amount' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_instant_qr_url_alias_redirects_to_qr_instant(): void
    {
        $response = $this->get('/instant-qr');

        $response->assertRedirect('/qr-instant');
    }

    public function test_instant_qr_auto_extracts_merchant_name_and_city_when_model_has_empty_names(): void
    {
        $user = User::factory()->create();
        UserQris::create([
            'user_id' => $user->id,
            'merchant_name' => '',
            'merchant_city' => '',
            'payload' => $this->sampleQrisPayload, // Payload contains 'AkuOnline IT Services' and 'KOTA TANGERANG'
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->get(route('instant_qr.index'));

        $response->assertStatus(200);
        $response->assertSee('AkuOnline IT Services');
        $response->assertSee('KOTA TANGERANG');
    }
}
