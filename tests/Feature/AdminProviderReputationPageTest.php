<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminSession;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminProviderReputationPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(AdminSession::class);

        $this->resetSchema();
        $this->createSchema();
    }

    public function test_admin_provider_reputation_page_renders_provider_review_data(): void
    {
        DB::table('service_providers')->insert([
            [
                'id' => 5,
                'first_name' => 'Kumi',
                'last_name' => 'Dequit',
                'email' => 'kumi@example.com',
                'phone' => '09944564055',
                'status' => 'Approved',
            ],
            [
                'id' => 6,
                'first_name' => 'Marlon',
                'last_name' => 'Gomez',
                'email' => 'marlon@example.com',
                'phone' => '09123456789',
                'status' => 'Approved',
            ],
        ]);

        DB::table('customers')->insert([
            [
                'id' => 9,
                'name' => 'Janze Salva',
                'email' => 'janze@example.com',
            ],
            [
                'id' => 10,
                'name' => 'Ronald Saballe',
                'email' => 'ronald@example.com',
            ],
        ]);

        DB::table('services')->insert([
            'id' => 2,
            'name' => 'Deep Cleaning',
        ]);

        DB::table('service_options')->insert([
            'id' => 21,
            'service_id' => 2,
            'label' => '2-Bedroom Apartment',
        ]);

        DB::table('bookings')->insert([
            [
                'id' => 1001,
                'provider_id' => 5,
                'customer_id' => 9,
                'service_id' => 2,
                'service_option_id' => 21,
                'reference_code' => 'PR-1001',
                'status' => 'completed',
                'booking_date' => now()->toDateString(),
            ],
            [
                'id' => 1002,
                'provider_id' => 6,
                'customer_id' => 10,
                'service_id' => 2,
                'service_option_id' => 21,
                'reference_code' => 'PR-1002',
                'status' => 'cancelled',
                'booking_date' => now()->toDateString(),
            ],
        ]);

        DB::table('reviews')->insert([
            [
                'id' => 501,
                'booking_id' => 1001,
                'provider_id' => 5,
                'customer_id' => 9,
                'rating' => 5,
                'comment' => 'Great service.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 502,
                'booking_id' => 1002,
                'provider_id' => 6,
                'customer_id' => 10,
                'rating' => 2,
                'comment' => 'Needs attention.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('admin.provider-reputation'));

        $response->assertOk();
        $response->assertSee('Provider Reputation');
        $response->assertSee('Kumi Dequit');
        $response->assertSee('Janze Salva');
        $response->assertSee('Recent Provider Reviews');
        $response->assertSee('View History');
    }

    private function resetSchema(): void
    {
        foreach ([
            'reviews',
            'bookings',
            'service_options',
            'services',
            'customers',
            'service_providers',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createSchema(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('label');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('service_option_id')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('status')->nullable();
            $table->date('booking_date')->nullable();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }
}
