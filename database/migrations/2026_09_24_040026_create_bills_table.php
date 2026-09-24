<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug', 16)->unique();
            $table->string('title');
            $table->text('qris_payload')->nullable();
            $table->string('qris_merchant_name')->nullable();
            $table->string('qris_merchant_city')->nullable();
            $table->string('qris_image_path')->nullable();
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('receipt_image_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
