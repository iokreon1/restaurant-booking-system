<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('table_id')->constrained();
            $table->json('items');
            $table->decimal('total_amount');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->integer('guest_count');
            $table->string('booking_status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
