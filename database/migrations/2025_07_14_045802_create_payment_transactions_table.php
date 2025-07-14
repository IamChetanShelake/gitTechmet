<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booked_hall_id')->nullable();
            $table->string('merchant_txn_no')->unique();
            $table->string('payphi_txn_no')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency_code')->default('356');
            $table->string('customer_email');
            $table->string('customer_mobile');
            $table->string('status')->default('initiated');
            $table->string('response_code')->nullable();
            $table->string('transaction_type')->default('SALE');
            $table->json('full_response')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
            
            $table->foreign('booked_hall_id')->references('id')->on('booked_halls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};
