<?php

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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->string('reference')->nullable()->after('method');
            $table->string('receipt_number')->nullable()->unique()->after('reference');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->text('notes')->nullable()->after('paid_at');
        });

        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash', 'card', 'sahal', 'e_dahab', 'mycash') NOT NULL DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash', 'card', 'mobile_money') NOT NULL DEFAULT 'cash'");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn(['reference', 'receipt_number', 'paid_at', 'notes']);
        });
    }
};
