<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'customer_latitude')) {
                    $table->decimal('customer_latitude', 10, 7)->nullable()->after('address');
                }

                if (!Schema::hasColumn('bookings', 'customer_longitude')) {
                    $table->decimal('customer_longitude', 10, 7)->nullable()->after('customer_latitude');
                }

                if (!Schema::hasColumn('bookings', 'formatted_address')) {
                    $table->text('formatted_address')->nullable()->after('customer_longitude');
                }
            });
        }

        if (!Schema::hasTable('booking_provider_locations')) {
            Schema::create('booking_provider_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('provider_id');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->text('formatted_address')->nullable();
                $table->boolean('is_tracking')->default(false);
                $table->timestamp('tracked_at')->nullable();
                $table->timestamp('stopped_at')->nullable();
                $table->timestamps();

                $table->unique('booking_id');
                $table->index(['provider_id', 'is_tracking']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_provider_locations')) {
            Schema::dropIfExists('booking_provider_locations');
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $dropColumns = [];

                foreach (['customer_latitude', 'customer_longitude', 'formatted_address'] as $column) {
                    if (Schema::hasColumn('bookings', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }
};
