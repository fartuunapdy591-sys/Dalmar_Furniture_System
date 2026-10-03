<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->date('expense_date');
            $table->enum('category', [
                'rent', 'electricity', 'water', 'internet', 'transport', 'salaries',
                'maintenance', 'marketing', 'office_supplies', 'fuel', 'tax', 'other',
            ])->default('other');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank', 'mobile_money', 'other'])->default('cash');
            $table->string('paid_to')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
