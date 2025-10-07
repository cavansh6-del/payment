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
        Schema::create('paygate_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->string('address_in', 2048);
            $table->string('polygon_address_in', 2048);
            $table->string('callback_url', 2048);
            $table->string('ipn_token', 2048);

            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');


            $table->string('value_coin')->nullable();
            $table->string('coin')->nullable();
            $table->string('txid_in', 2048)->nullable();
            $table->string('txid_out', 2048)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paygate_wallets');
    }
};
