<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('type')->nullable();
            $table->text('message');
            $table->string('reference_code')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('provider_id');
            $table->index('reference_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_notifications');
    }
};