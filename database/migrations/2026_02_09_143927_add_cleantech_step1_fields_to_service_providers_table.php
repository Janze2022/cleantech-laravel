<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {

            if (!Schema::hasColumn('service_providers', 'first_name')) {
                $table->string('first_name', 50)->nullable();
            }

            if (!Schema::hasColumn('service_providers', 'middle_name')) {
                $table->string('middle_name', 50)->nullable();
            }

            if (!Schema::hasColumn('service_providers', 'last_name')) {
                $table->string('last_name', 50)->nullable();
            }

        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {

            if (Schema::hasColumn('service_providers', 'middle_name')) {
                $table->dropColumn('middle_name');
            }

        });
    }
};