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
        Schema::create('freemius_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('billable');
            $table->string('type');
            $table->string('freemius_id')->unique();
            $table->string('status');
            $table->string('product_id');
            $table->string('plan_id');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freemius_subscriptions');
    }
};
