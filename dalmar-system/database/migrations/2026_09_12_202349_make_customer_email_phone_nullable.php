<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE customers MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE customers MODIFY phone VARCHAR(30) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE customers SET email = CONCAT('unknown_', id, '@dalmar.local') WHERE email IS NULL");
        DB::statement("UPDATE customers SET phone = 'N/A' WHERE phone IS NULL");
        DB::statement('ALTER TABLE customers MODIFY email VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE customers MODIFY phone VARCHAR(30) NOT NULL');
    }
};
