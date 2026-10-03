<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('invoices', 'receipts');

        Schema::table('receipts', function (Blueprint $table) {
            $table->renameColumn('invoice_number', 'receipt_number');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('invoice_id', 'receipt_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('receipt_id', 'invoice_id');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->renameColumn('receipt_number', 'invoice_number');
        });

        Schema::rename('receipts', 'invoices');
    }
};