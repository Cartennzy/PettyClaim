<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('merchant_name');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('receipt_image_path');
            $table->string('phash_value', 64)->nullable();
            $table->boolean('is_flagged_duplicate')->default(false);
            $table->enum('status', [
                'PENDING',
                'PENDING_VERIFICATION',
                'VERIFIED',
                'APPROVED',
                'REJECTED'
            ])->default('PENDING_VERIFICATION');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};