<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_providers', 'middle_name')) {
                $table->string('middle_name', 50)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('service_providers', 'citizenship')) {
                $table->string('citizenship', 80)->nullable()->after('email');
            }
            if (!Schema::hasColumn('service_providers', 'is_stateless')) {
                $table->boolean('is_stateless')->default(0)->after('citizenship');
            }
            if (!Schema::hasColumn('service_providers', 'is_refugee')) {
                $table->boolean('is_refugee')->default(0)->after('is_stateless');
            }
            if (!Schema::hasColumn('service_providers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('is_refugee');
            }
            if (!Schema::hasColumn('service_providers', 'civil_status')) {
                $table->string('civil_status', 20)->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('service_providers', 'gender')) {
                $table->string('gender', 10)->nullable()->after('civil_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $cols = ['middle_name','citizenship','is_stateless','is_refugee','date_of_birth','civil_status','gender'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('service_providers', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
