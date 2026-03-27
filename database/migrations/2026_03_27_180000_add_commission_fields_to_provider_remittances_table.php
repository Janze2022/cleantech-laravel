<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provider_remittances')) {
            return;
        }

        Schema::table('provider_remittances', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_remittances', 'gross_amount')) {
                $table->decimal('gross_amount', 12, 2)->nullable()->after('recorded_amount');
            }

            if (!Schema::hasColumn('provider_remittances', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 4)->nullable()->after('gross_amount');
            }

            if (!Schema::hasColumn('provider_remittances', 'commission_amount')) {
                $table->decimal('commission_amount', 12, 2)->nullable()->after('commission_rate');
            }

            if (!Schema::hasColumn('provider_remittances', 'net_amount')) {
                $table->decimal('net_amount', 12, 2)->nullable()->after('commission_amount');
            }
        });

        DB::table('provider_remittances')->update([
            'gross_amount' => DB::raw('COALESCE(recorded_amount, 0)'),
            'commission_rate' => DB::raw('0.1000'),
            'commission_amount' => DB::raw('ROUND(COALESCE(recorded_amount, 0) * 0.10, 2)'),
            'net_amount' => DB::raw('ROUND(COALESCE(recorded_amount, 0) - (COALESCE(recorded_amount, 0) * 0.10), 2)'),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('provider_remittances')) {
            return;
        }

        Schema::table('provider_remittances', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['gross_amount', 'commission_rate', 'commission_amount', 'net_amount'] as $column) {
                if (Schema::hasColumn('provider_remittances', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
