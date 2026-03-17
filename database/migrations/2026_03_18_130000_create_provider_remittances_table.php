<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_remittances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->index();
            $table->date('remit_date')->index();
            $table->string('status', 20)->default('remitted');
            $table->decimal('recorded_amount', 12, 2)->default(0);
            $table->timestamp('remitted_at')->nullable();
            $table->unsignedBigInteger('remitted_by_admin_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'remit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_remittances');
    }
};
