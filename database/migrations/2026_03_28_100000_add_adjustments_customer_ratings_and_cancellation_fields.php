<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->text('cancellation_reason')->nullable()->after('status');
                });
            }

            if (!Schema::hasColumn('bookings', 'cancelled_by_role')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->string('cancelled_by_role', 30)->nullable()->after('cancellation_reason');
                });
            }

            if (!Schema::hasColumn('bookings', 'adjustment_status')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->string('adjustment_status', 40)->nullable()->after('cancelled_by_role');
                });
            }
        }

        if (Schema::hasTable('reviews')) {
            if (!Schema::hasColumn('reviews', 'attachment_path')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->text('attachment_path')->nullable()->after('comment');
                });
            }

            if (!Schema::hasColumn('reviews', 'attachment_name')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->string('attachment_name', 255)->nullable()->after('attachment_path');
                });
            }

            if (!Schema::hasColumn('reviews', 'attachment_mime')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->string('attachment_mime', 120)->nullable()->after('attachment_name');
                });
            }
        }

        if (!Schema::hasTable('booking_adjustments')) {
            Schema::create('booking_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->unique();
                $table->unsignedBigInteger('provider_id');
                $table->unsignedBigInteger('customer_id');
                $table->string('original_service_name', 160)->nullable();
                $table->text('original_option_summary')->nullable();
                $table->decimal('original_price', 10, 2)->default(0);
                $table->string('proposed_service_name', 160)->nullable();
                $table->text('proposed_scope_summary')->nullable();
                $table->decimal('additional_fee', 10, 2)->default(0);
                $table->decimal('proposed_total', 10, 2)->default(0);
                $table->decimal('price_increase_percent', 6, 2)->default(0);
                $table->longText('reason_payload')->nullable();
                $table->text('other_reason')->nullable();
                $table->text('provider_note')->nullable();
                $table->text('customer_response_note')->nullable();
                $table->text('evidence_path')->nullable();
                $table->string('evidence_name', 255)->nullable();
                $table->string('evidence_mime', 120)->nullable();
                $table->string('status', 50)->default('pending_adjustment_approval');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('booking_adjustment_logs')) {
            Schema::create('booking_adjustment_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_adjustment_id');
                $table->unsignedBigInteger('booking_id');
                $table->string('actor_role', 30);
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('action', 80);
                $table->text('note')->nullable();
                $table->longText('payload')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_ratings')) {
            Schema::create('customer_ratings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->unique();
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
                $table->text('attachment_path')->nullable();
                $table->string('attachment_name', 255)->nullable();
                $table->string('attachment_mime', 120)->nullable();
                $table->unsignedInteger('edit_count')->default(0);
                $table->timestamp('editable_until')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_ratings')) {
            Schema::dropIfExists('customer_ratings');
        }

        if (Schema::hasTable('booking_adjustment_logs')) {
            Schema::dropIfExists('booking_adjustment_logs');
        }

        if (Schema::hasTable('booking_adjustments')) {
            Schema::dropIfExists('booking_adjustments');
        }

        if (Schema::hasTable('reviews')) {
            foreach (['attachment_mime', 'attachment_name', 'attachment_path'] as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    Schema::table('reviews', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('bookings')) {
            foreach (['adjustment_status', 'cancelled_by_role', 'cancellation_reason'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    Schema::table('bookings', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
