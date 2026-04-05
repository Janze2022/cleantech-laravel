<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_ratings')) {
            return;
        }

        if (!Schema::hasColumn('customer_ratings', 'admin_reviewed_at')) {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->timestamp('admin_reviewed_at')->nullable()->after('editable_until');
            });
        }

        if (!Schema::hasColumn('customer_ratings', 'admin_reviewed_by')) {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_reviewed_by')->nullable()->after('admin_reviewed_at');
            });
        }

        if (!Schema::hasColumn('customer_ratings', 'admin_review_note')) {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->text('admin_review_note')->nullable()->after('admin_reviewed_by');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('customer_ratings')) {
            return;
        }

        foreach (['admin_review_note', 'admin_reviewed_by', 'admin_reviewed_at'] as $column) {
            if (Schema::hasColumn('customer_ratings', $column)) {
                Schema::table('customer_ratings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
