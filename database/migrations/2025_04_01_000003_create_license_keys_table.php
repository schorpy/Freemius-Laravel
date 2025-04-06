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
        Schema::create('freemius_license_keys', function (Blueprint $table) {
            $table->id();
            $table->string('freemius_id')->unique();
            $table->string('license_key')->unique();
            $table->string('status');
            $table->string('order_id')->index();
            $table->string('product_id')->index();
            $table->boolean('disabled');
            $table->integer('activation_limit')->nullable();
            $table->integer('instances_count');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('freemius_id')->on('freemius_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freemius_license_keys');
    }
};
