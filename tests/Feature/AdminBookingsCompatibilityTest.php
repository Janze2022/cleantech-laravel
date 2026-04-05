<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminSession;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminBookingsCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(AdminSession::class);

        $this->resetSchema();
        $this->createCompatibilitySchema();
    }

    public function test_admin_bookings_index_handles_adjustment_tables_with_missing_optional_columns(): void
    {
        DB::table('customers')->insert([
            'id' => 9,
            'full_name' => 'Chris Customer',
        ]);

        DB::table('service_providers')->insert([
            'id' => 5,
            'name' => 'Pat Provider',
        ]);

        DB::table('services')->insert([
            'id' => 2,
            'service_name' => 'Deep Cleaning',
        ]);

        DB::table('service_options')->insert([
            'id' => 21,
            'service_id' => 2,
            'option_name' => '2-Bedroom Apartment',
        ]);

        DB::table('bookings')->insert([
            'id' => 1001,
            'customer_id' => 9,
            'provider_id' => 5,
            'service_id' => 2,
            'service_option_id' => 21,
            'reference_code' => 'ADM-1001',
            'status' => 'confirmed',
            'price' => 4000,
            'address' => 'Purok 1',
            'contact_phone' => '09123456789',
            'house_type' => 'medium',
            'booking_date' => now()->toDateString(),
            'time_start' => '09:00:00',
            'time_end' => '11:00:00',
            'adjustment_status' => 'pending_adjustment_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_adjustments')->insert([
            'id' => 501,
            'booking_id' => 1001,
            'provider_id' => 5,
            'customer_id' => 9,
            'original_service_name' => 'General Cleaning',
            'original_option_summary' => '1-Bedroom Apartment',
            'original_price' => 3300,
            'proposed_service_name' => 'Deep Cleaning',
            'proposed_scope_summary' => 'Actual onsite booking: Deep Cleaning / 2-Bedroom Apartment.',
            'additional_fee' => 700,
            'proposed_total' => 4000,
            'price_increase_percent' => 21.2,
            'status' => 'pending_adjustment_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_adjustment_logs')->insert([
            'booking_adjustment_id' => 501,
            'actor_role' => 'provider',
            'action' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.bookings'));

        $response->assertOk();
        $response->assertSee('ADM-1001');
        $response->assertSee('Pending Adjustment Approval');
        $response->assertSee('Deep Cleaning');
    }

    private function resetSchema(): void
    {
        foreach ([
            'booking_adjustment_logs',
            'booking_adjustments',
            'bookings',
            'service_options',
            'services',
            'service_providers',
            'customers',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createCompatibilitySchema(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
        });

        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
        });

        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('option_name');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('service_option_id')->nullable();
            $table->string('reference_code')->unique();
            $table->string('status');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('house_type')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('time_start')->nullable();
            $table->string('time_end')->nullable();
            $table->string('adjustment_status')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('original_service_name')->nullable();
            $table->text('original_option_summary')->nullable();
            $table->decimal('original_price', 10, 2)->default(0);
            $table->string('proposed_service_name')->nullable();
            $table->text('proposed_scope_summary')->nullable();
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->decimal('proposed_total', 10, 2)->default(0);
            $table->decimal('price_increase_percent', 8, 2)->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_adjustment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_adjustment_id');
            $table->string('actor_role')->nullable();
            $table->string('action')->nullable();
            $table->timestamps();
        });
    }
}
