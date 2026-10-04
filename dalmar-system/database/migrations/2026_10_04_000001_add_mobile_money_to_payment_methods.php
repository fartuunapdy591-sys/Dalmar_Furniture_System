<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Orders can now be paid with Cash, Mobile Money or e-Dahab. Older methods
        // stay in the list so existing records remain valid.
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash', 'sahal', 'e_dahab', 'mycash', 'card', 'mobile_money', 'paid') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash', 'card', 'sahal', 'e_dahab', 'mycash', 'mobile_money') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("UPDATE orders SET payment_method = 'mycash' WHERE payment_method = 'mobile_money'");
        DB::statement("UPDATE payments SET method = 'mycash' WHERE method = 'mobile_money'");
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash', 'sahal', 'e_dahab', 'mycash', 'card', 'paid') NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash', 'card', 'sahal', 'e_dahab', 'mycash') NOT NULL DEFAULT 'cash'");
    }
};
