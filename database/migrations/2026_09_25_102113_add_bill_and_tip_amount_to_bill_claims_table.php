<?php

use App\Models\BillClaim;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bill_claims', function (Blueprint $table) {
            $table->decimal('bill_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('tip_amount', 12, 2)->default(0)->after('bill_amount');
        });

        // Backfill existing claims
        $claims = BillClaim::with(['bill.items', 'claimItems.item'])->get();
        foreach ($claims as $claim) {
            $exact = (float) $claim->exact_payable;
            $total = (float) $claim->amount;
            $billAmount = min($total, $exact);
            $tipAmount = max(0, $total - $billAmount);

            DB::table('bill_claims')
                ->where('id', $claim->id)
                ->update([
                    'bill_amount' => $billAmount,
                    'tip_amount' => $tipAmount,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_claims', function (Blueprint $table) {
            $table->dropColumn(['bill_amount', 'tip_amount']);
        });
    }
};
