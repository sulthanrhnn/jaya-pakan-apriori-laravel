<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->string('source', 20)->default('web')->index();
            $table->string('status', 40)->default('pending_verification')->index();
            $table->string('payment_status', 20)->default('pending')->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->text('customer_address')->nullable();
            $table->text('customer_note')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('payment_method', 30)->default('bank_transfer');
            $table->string('payment_proof_path')->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('stock_released_at')->nullable();
            
            // REVISI: Menambahkan useCurrent() untuk memberikan nilai default waktu saat ini
            $table->timestamp('ordered_at')->useCurrent()->index(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};