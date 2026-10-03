<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('subtotal');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
            $table->foreignId('discount_by')->nullable()->after('discount_amount')->constrained('users')->nullOnDelete();
            $table->boolean('is_credit_sale')->default(false)->after('discount_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_by');
            $table->dropColumn(['subtotal', 'discount_type', 'discount_value', 'discount_amount', 'is_credit_sale']);
        });
    }
};
