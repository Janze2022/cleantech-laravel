<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_adjustments')) {
            return;
        }

        Schema::table('booking_adjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_adjustments', 'proposed_service_option_id')) {
                $table->unsignedBigInteger('proposed_service_option_id')
                    ->nullable()
                    ->after('proposed_service_name');
            }

            if (!Schema::hasColumn('booking_adjustments', 'proposed_option_ids_payload')) {
                $table->longText('proposed_option_ids_payload')
                    ->nullable()
                    ->after('proposed_service_option_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_adjustments')) {
            return;
        }

        Schema::table('booking_adjustments', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('booking_adjustments', 'proposed_service_option_id')) {
                $dropColumns[] = 'proposed_service_option_id';
            }

            if (Schema::hasColumn('booking_adjustments', 'proposed_option_ids_payload')) {
                $dropColumns[] = 'proposed_option_ids_payload';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
