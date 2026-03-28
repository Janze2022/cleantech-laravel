<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_rating_logs')) {
            Schema::create('customer_rating_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_rating_id');
                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('provider_id');
                $table->string('actor_role', 30);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('action', 80);
                $table->longText('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_rating_logs');
    }
};
