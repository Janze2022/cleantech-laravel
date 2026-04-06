<?php

namespace Tests\Feature;

use App\Http\Middleware\ProviderSession;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProviderCustomerRatingsIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ProviderSession::class,
            ValidateCsrfToken::class,
        ]);

        $this->resetSchema();
        $this->createSchema();
        $this->seedCatalog();
    }

    public function test_index_defaults_to_pending_first_with_alphabetical_customer_sorting(): void
    {
        $aaron = $this->createCustomer(11, 'Aaron Alpha', 'aaron@example.com');
        $zara = $this->createCustomer(12, 'Zara Zenith', 'zara@example.com');
        $brenda = $this->createCustomer(13, 'Brenda Bloom', 'brenda@example.com');
        $charlie = $this->createCustomer(14, 'Charlie Cruz', 'charlie@example.com');

        $this->createBooking(1001, $aaron, 'CT-PENDING-A', 'completed');
        $this->createBooking(1002, $zara, 'CT-PENDING-Z', 'paid');
        $this->createBooking(1003, $brenda, 'CT-SAVED-B', 'completed', 4);
        $this->createBooking(1004, $charlie, 'CT-SAVED-C', 'paid', 5);

        $response = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
            'name' => 'Kumi',
        ])->get(route('provider.customer-ratings'));

        $response->assertOk();
        $response->assertSeeText('Pending Reviews');
        $response->assertSeeText('Saved Reviews');
        $response->assertSeeTextInOrder([
            'Aaron Alpha',
            'Zara Zenith',
            'Brenda Bloom',
            'Charlie Cruz',
        ]);
    }

    public function test_index_supports_search_and_rating_sorting_while_keeping_pending_reviews_first(): void
    {
        $pending = $this->createCustomer(21, 'Ronald Pending', 'pending@example.com');
        $low = $this->createCustomer(22, 'Ronald Low', 'low@example.com');
        $high = $this->createCustomer(23, 'Ronald High', 'high@example.com');
        $hidden = $this->createCustomer(24, 'Alice Hidden', 'alice@example.com');

        $this->createBooking(2001, $pending, 'CT-RON-PENDING', 'completed');
        $this->createBooking(2002, $low, 'CT-RON-LOW', 'completed', 2);
        $this->createBooking(2003, $high, 'CT-RON-HIGH', 'paid', 5);
        $this->createBooking(2004, $hidden, 'CT-ALICE', 'completed', 4);

        $response = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
            'name' => 'Kumi',
        ])->get(route('provider.customer-ratings', [
            'q' => 'ronald',
            'sort' => 'rating_low',
        ]));

        $response->assertOk();
        $response->assertDontSeeText('Alice Hidden');
        $response->assertSeeTextInOrder([
            'Ronald Pending',
            'Ronald Low',
            'Ronald High',
        ]);
    }

    public function test_submitted_ratings_are_view_only_in_history_and_ratings_pages(): void
    {
        $ratedCustomer = $this->createCustomer(31, 'Viewed Only', 'view@example.com');

        $this->createBooking(3001, $ratedCustomer, 'CT-VIEW-ONLY', 'completed', 5);

        $historyResponse = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
            'name' => 'Kumi',
        ])->get(route('provider.bookings.history'));

        $historyResponse->assertOk();
        $historyResponse->assertSeeText('View Rating');
        $historyResponse->assertDontSeeText('Edit Rating');

        $ratingsResponse = $this->withSession([
            'provider_id' => 5,
            'role' => 'provider',
            'name' => 'Kumi',
        ])->get(route('provider.customer-ratings', ['booking' => 3001]));

        $ratingsResponse->assertOk();
        $ratingsResponse->assertSeeText('Review saved on');
        $ratingsResponse->assertDontSeeText('Update Review');
    }

    private function resetSchema(): void
    {
        foreach ([
            'customer_ratings',
            'bookings',
            'service_options',
            'services',
            'customers',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createSchema(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

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

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('service_option_id')->nullable();
            $table->string('reference_code')->unique();
            $table->string('status');
            $table->date('booking_date')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('customer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedTinyInteger('rating');
            $table->boolean('booking_details_accurate')->default(false);
            $table->boolean('respectful')->default(false);
            $table->boolean('easy_to_communicate')->default(false);
            $table->boolean('paid_reliably')->default(false);
            $table->boolean('unexpected_extra_work')->default(false);
            $table->boolean('flag_understated_area')->default(false);
            $table->boolean('flag_hidden_sections')->default(false);
            $table->boolean('flag_misleading_request')->default(false);
            $table->boolean('flag_difficult_behavior')->default(false);
            $table->boolean('flag_payment_issue')->default(false);
            $table->boolean('flag_last_minute_changes')->default(false);
            $table->text('comment')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->unsignedInteger('edit_count')->default(0);
            $table->timestamp('editable_until')->nullable();
            $table->timestamps();
        });
    }

    private function seedCatalog(): void
    {
        DB::table('services')->insert([
            'id' => 1,
            'name' => 'Deep Cleaning',
            'base_price' => 2000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_options')->insert([
            'id' => 11,
            'service_id' => 1,
            'label' => '2-Bedroom Apartment',
            'price_addition' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCustomer(int $id, string $name, string $email): int
    {
        DB::table('customers')->insert([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone' => '09170000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createBooking(
        int $id,
        int $customerId,
        string $referenceCode,
        string $status,
        ?int $rating = null
    ): void {
        DB::table('bookings')->insert([
            'id' => $id,
            'customer_id' => $customerId,
            'provider_id' => 5,
            'service_id' => 1,
            'service_option_id' => 11,
            'reference_code' => $referenceCode,
            'status' => $status,
            'booking_date' => '2026-04-06',
            'price' => 4000,
            'created_at' => now(),
            'updated_at' => now()->addMinutes($id),
        ]);

        if ($rating === null) {
            return;
        }

        DB::table('customer_ratings')->insert([
            'booking_id' => $id,
            'customer_id' => $customerId,
            'provider_id' => 5,
            'rating' => $rating,
            'respectful' => true,
            'comment' => 'Saved review for testing.',
            'edit_count' => 0,
            'editable_until' => now()->addHours(12),
            'created_at' => now(),
            'updated_at' => now()->addMinutes($rating),
        ]);
    }
}
