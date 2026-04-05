<?php

namespace Tests\Feature;

use App\Http\Middleware\CustomerSession;
use App\Http\Middleware\ProviderSession;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingAdjustmentServiceSelectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ProviderSession::class,
            CustomerSession::class,
            ValidateCsrfToken::class,
        ]);
        Storage::fake('public');

        $this->resetSchema();
        $this->createBaseSchema();
    }

    public function test_provider_submit_adjustment_uses_selected_service_scope_and_heavy_soiling_fee(): void
    {
        $this->seedProviderAndCustomer();
        $this->seedServiceCatalog([
            ['id' => 1, 'name' => 'General Cleaning', 'base_price' => 1800],
            ['id' => 2, 'name' => 'Deep Cleaning', 'base_price' => 2000],
        ], [
            ['id' => 11, 'service_id' => 1, 'label' => 'Studio', 'price_addition' => 200],
            ['id' => 21, 'service_id' => 2, 'label' => 'Small Home', 'price_addition' => 100],
        ]);

        DB::table('bookings')->insert([
            'id' => 1001,
            'customer_id' => 9,
            'provider_id' => 5,
            'reference_code' => 'REF-1001',
            'status' => 'in_progress',
            'price' => 2000,
            'service_id' => 1,
            'service_option_id' => 11,
            'adjustment_status' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
        ])->post(route('provider.bookings.adjustment.submit', 'REF-1001'), [
            'reason_codes' => ['larger_area', 'heavy_soiling'],
            'corrected_service_id' => 2,
            'corrected_option_id' => 21,
            'provider_note' => 'The booking needed deep cleaning onsite.',
            'evidence' => UploadedFile::fake()->image('mismatch.jpg'),
        ]);

        $response
            ->assertRedirect(route('provider.bookings.show', 'REF-1001'))
            ->assertSessionHas('success');

        $adjustment = DB::table('booking_adjustments')
            ->where('booking_id', 1001)
            ->first();

        $this->assertNotNull($adjustment);
        $this->assertSame('Deep Cleaning', $adjustment->proposed_service_name);
        $this->assertSame(21, (int) $adjustment->proposed_service_option_id);
        $this->assertSame([21], json_decode((string) $adjustment->proposed_option_ids_payload, true));
        $this->assertSame(400.0, (float) $adjustment->additional_fee);
        $this->assertSame(2400.0, (float) $adjustment->proposed_total);
        $this->assertSame('pending_adjustment_approval', $adjustment->status);
        $this->assertSame('pending_adjustment_approval', DB::table('bookings')->where('id', 1001)->value('adjustment_status'));
        Storage::disk('public')->assertExists((string) $adjustment->evidence_path);
    }

    public function test_customer_accept_adjustment_applies_changed_service_and_all_selected_options(): void
    {
        Schema::create('booking_service_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('service_option_id');
            $table->timestamps();
        });

        $this->seedProviderAndCustomer();
        $this->seedServiceCatalog([
            ['id' => 1, 'name' => 'General Cleaning', 'base_price' => 1800],
            ['id' => 3, 'name' => 'Specific Area Cleaning', 'base_price' => 1700],
        ], [
            ['id' => 11, 'service_id' => 1, 'label' => 'Studio', 'price_addition' => 200],
            ['id' => 31, 'service_id' => 3, 'label' => 'Kitchen', 'price_addition' => 250],
            ['id' => 32, 'service_id' => 3, 'label' => 'Bathroom', 'price_addition' => 300],
        ]);

        DB::table('bookings')->insert([
            'id' => 1002,
            'customer_id' => 9,
            'provider_id' => 5,
            'reference_code' => 'REF-1002',
            'status' => 'confirmed',
            'price' => 2000,
            'service_id' => 1,
            'service_option_id' => 11,
            'adjustment_status' => 'pending_adjustment_approval',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_service_options')->insert([
            'booking_id' => 1002,
            'service_option_id' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_adjustments')->insert([
            'booking_id' => 1002,
            'provider_id' => 5,
            'customer_id' => 9,
            'original_service_name' => 'General Cleaning',
            'original_option_summary' => 'Studio',
            'original_price' => 2000,
            'proposed_service_name' => 'Specific Area Cleaning',
            'proposed_service_option_id' => 31,
            'proposed_option_ids_payload' => json_encode([31, 32]),
            'proposed_scope_summary' => 'Actual onsite booking: Specific Area Cleaning / Kitchen, Bathroom.',
            'additional_fee' => 250,
            'proposed_total' => 2250,
            'price_increase_percent' => 12.5,
            'reason_payload' => json_encode(['additional_rooms']),
            'other_reason' => null,
            'provider_note' => 'Two separate areas were requested onsite.',
            'customer_response_note' => null,
            'evidence_path' => 'booking-adjustments/evidence/existing-proof.jpg',
            'evidence_name' => 'existing-proof.jpg',
            'evidence_mime' => 'image/jpeg',
            'status' => 'pending_adjustment_approval',
            'resolved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'user_id' => 9,
            'role' => 'customer',
        ])->post(route('customer.bookings.adjustment.respond', 'REF-1002'), [
            'response' => 'accept',
            'customer_response_note' => 'Understood, please continue.',
        ]);

        $response
            ->assertRedirect(route('customer.bookings.show', 'REF-1002'))
            ->assertSessionHas('success');

        $booking = DB::table('bookings')->where('id', 1002)->first();
        $this->assertSame(3, (int) $booking->service_id);
        $this->assertSame(31, (int) $booking->service_option_id);
        $this->assertSame(2250.0, (float) $booking->price);
        $this->assertSame('adjustment_accepted', $booking->adjustment_status);

        $selectedOptionIds = DB::table('booking_service_options')
            ->where('booking_id', 1002)
            ->orderBy('service_option_id')
            ->pluck('service_option_id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $this->assertSame([31, 32], $selectedOptionIds);

        $adjustment = DB::table('booking_adjustments')
            ->where('booking_id', 1002)
            ->first();

        $this->assertSame('adjustment_accepted', $adjustment->status);
        $this->assertSame('Understood, please continue.', $adjustment->customer_response_note);
    }

    public function test_provider_booking_page_exposes_service_selector_and_service_catalog_payload(): void
    {
        $this->seedProviderAndCustomer();
        $this->seedServiceCatalog([
            ['id' => 1, 'name' => 'General Cleaning', 'base_price' => 1800],
            ['id' => 2, 'name' => 'Deep Cleaning', 'base_price' => 2000],
            ['id' => 3, 'name' => 'Specific Area Cleaning', 'base_price' => 1700],
        ], [
            ['id' => 11, 'service_id' => 1, 'label' => 'Studio', 'price_addition' => 200],
            ['id' => 21, 'service_id' => 2, 'label' => 'Small Home', 'price_addition' => 100],
            ['id' => 31, 'service_id' => 3, 'label' => 'Kitchen', 'price_addition' => 250],
            ['id' => 32, 'service_id' => 3, 'label' => 'Bathroom', 'price_addition' => 300],
        ]);

        DB::table('bookings')->insert([
            'id' => 1003,
            'customer_id' => 9,
            'provider_id' => 5,
            'reference_code' => 'REF-1003',
            'status' => 'in_progress',
            'price' => 2000,
            'service_id' => 1,
            'service_option_id' => 11,
            'adjustment_status' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
        ])->get(route('provider.bookings.show', 'REF-1003'));

        $response->assertOk();
        $response->assertSee('Actual Onsite Service');
        $response->assertSee('Actual Onsite Size / Components');
        $response->assertSee('name="corrected_service_id"', false);
        $response->assertSee('data-service-catalog=', false);
        $response->assertSee('Deep Cleaning');
        $response->assertSee('Specific Area Cleaning');
    }

    private function resetSchema(): void
    {
        foreach ([
            'booking_adjustment_logs',
            'booking_adjustments',
            'booking_service_options',
            'notifications',
            'bookings',
            'service_options',
            'services',
            'reviews',
            'service_providers',
            'customers',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createBaseSchema(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('label');
            $table->decimal('price_addition', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('service_option_id')->nullable();
            $table->string('reference_code')->unique();
            $table->string('status');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('adjustment_status')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('time_start')->nullable();
            $table->string('time_end')->nullable();
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
            $table->unsignedBigInteger('proposed_service_option_id')->nullable();
            $table->text('proposed_option_ids_payload')->nullable();
            $table->text('proposed_scope_summary')->nullable();
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->decimal('proposed_total', 10, 2)->default(0);
            $table->decimal('price_increase_percent', 8, 2)->default(0);
            $table->text('reason_payload')->nullable();
            $table->text('other_reason')->nullable();
            $table->text('provider_note')->nullable();
            $table->text('customer_response_note')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('evidence_name')->nullable();
            $table->string('evidence_mime')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_adjustment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_adjustment_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('actor_role');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->text('note')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reference_code')->nullable();
            $table->text('message');
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    private function seedProviderAndCustomer(): void
    {
        DB::table('service_providers')->insert([
            'id' => 5,
            'first_name' => 'Pat',
            'last_name' => 'Provider',
            'phone' => '09123456789',
            'city' => 'Butuan',
            'province' => 'Agusan del Norte',
            'status' => 'Approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            'id' => 9,
            'name' => 'Chris Customer',
            'email' => 'customer@example.com',
            'phone' => '09987654321',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedServiceCatalog(array $services, array $options): void
    {
        DB::table('services')->insert(array_map(function (array $service) {
            return [
                ...$service,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $services));

        DB::table('service_options')->insert(array_map(function (array $option) {
            return [
                ...$option,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $options));
    }
}
