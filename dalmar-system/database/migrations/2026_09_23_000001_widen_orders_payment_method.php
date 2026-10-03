<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The "Add Order" form offers the same payment methods as POS (cash, sahal,
        // e_dahab, mycash, card), but this column was still limited to the old
        // ('cash', 'paid') values, so selecting anything else failed with a
        // "Data truncated for column 'payment_method'" error.
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash', 'sahal', 'e_dahab', 'mycash', 'card', 'paid') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash', 'paid') NOT NULL DEFAULT 'cash'");
    }
};
