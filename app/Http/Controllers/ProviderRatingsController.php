<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProviderRatingsController extends Controller
{
    private function providerId(): int
    {
        $providerId = (int) session('provider_id');

        if (!$providerId) {
            abort(403, 'Provider session missing.');
        }

        return $providerId;
    }

    public function index(Request $request)
    {
        $providerId = $this->providerId();

        // Rating summary
        $ratingSummary = DB::table('reviews')
            ->where('provider_id', $providerId)
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
            ->first();

        if (!$ratingSummary) {
            $ratingSummary = (object) [
                'avg'   => 0,
                'count' => 0,
            ];
        }

        $count = (int) ($ratingSummary->count ?? 0);

        // Rating breakdown (always return 5..1)
        $rawBreakdown = DB::table('reviews')
            ->where('provider_id', $providerId)
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->get();

        $breakdown = collect([5, 4, 3, 2, 1])->map(function ($star) use ($rawBreakdown) {
            $row = $rawBreakdown->firstWhere('rating', $star);

            return (object) [
                'star' => $star,
                'cnt'  => (int) ($row->cnt ?? 0),
            ];
        });

        // No ratings yet
        if ($count <= 0) {
            $reviews = collect();
            return view('provider.ratings', compact('ratingSummary', 'breakdown', 'reviews'));
        }

        // Build customer name safely depending on available columns
        if (
            Schema::hasColumn('customers', 'first_name') &&
            Schema::hasColumn('customers', 'last_name')
        ) {
            $customerNameSql = "
                COALESCE(
                    NULLIF(TRIM(CONCAT(IFNULL(c.first_name,''), ' ', IFNULL(c.last_name,''))), ''),
                    NULLIF(TRIM(c.name), ''),
                    NULLIF(TRIM(c.email), ''),
                    'Customer'
                )
            ";
        } elseif (Schema::hasColumn('customers', 'name')) {
            $customerNameSql = "
                COALESCE(
                    NULLIF(TRIM(c.name), ''),
                    NULLIF(TRIM(c.email), ''),
                    'Customer'
                )
            ";
        } else {
            $customerNameSql = "
                COALESCE(
                    NULLIF(TRIM(c.email), ''),
                    'Customer'
                )
            ";
        }

        // Reviews list
        $reviewsQuery = DB::table('reviews as r')
            ->join('customers as c', 'c.id', '=', 'r.customer_id')
            ->where('r.provider_id', $providerId)
            ->whereNotNull('r.rating')
            ->where('r.rating', '>', 0)
            ->orderByDesc('r.created_at')
            ->select([
                'r.id',
                'r.customer_id',
                'r.rating',
                'r.comment',
                'r.created_at',
                Schema::hasColumn('reviews', 'attachment_path')
                    ? 'r.attachment_path'
                    : DB::raw('NULL as attachment_path'),
                Schema::hasColumn('reviews', 'attachment_name')
                    ? 'r.attachment_name'
                    : DB::raw('NULL as attachment_name'),
                Schema::hasColumn('reviews', 'attachment_mime')
                    ? 'r.attachment_mime'
                    : DB::raw('NULL as attachment_mime'),
                DB::raw($customerNameSql . ' as customer_name'),
                'c.email as customer_email',
            ]);

        if (
            Schema::hasColumn('reviews', 'booking_id') &&
            Schema::hasTable('bookings')
        ) {
            $reviewsQuery->leftJoin('bookings as b', 'b.id', '=', 'r.booking_id');

            if (Schema::hasTable('services') && Schema::hasColumn('bookings', 'service_id')) {
                $reviewsQuery->leftJoin('services as s', 's.id', '=', 'b.service_id');
                $reviewsQuery->addSelect(DB::raw("COALESCE(NULLIF(TRIM(s.name), ''), 'Service') as service_name"));
            } else {
                $reviewsQuery->addSelect(DB::raw("'Service' as service_name"));
            }

            if (Schema::hasTable('service_options') && Schema::hasColumn('bookings', 'service_option_id')) {
                $areasSub = $this->bookingAreasSubquery();

                $reviewsQuery->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id');
                $reviewsQuery->leftJoinSub($areasSub, 'areas', function ($join) {
                    $join->on('areas.booking_id', '=', 'b.id');
                });

                $reviewsQuery->addSelect(DB::raw("COALESCE(areas.areas_label, o.label) as option_name"));
            } else {
                $reviewsQuery->addSelect(DB::raw("NULL as option_name"));
            }

            if (Schema::hasColumn('bookings', 'reference_code')) {
                $reviewsQuery->addSelect('b.reference_code');
            } elseif (Schema::hasColumn('reviews', 'reference_code')) {
                $reviewsQuery->addSelect('r.reference_code');
            } else {
                $reviewsQuery->addSelect(DB::raw("NULL as reference_code"));
            }

            if (Schema::hasColumn('bookings', 'booking_date')) {
                $reviewsQuery->addSelect('b.booking_date');
            } else {
                $reviewsQuery->addSelect(DB::raw("NULL as booking_date"));
            }
        } else {
            $reviewsQuery->addSelect(DB::raw("'Service' as service_name"));
            $reviewsQuery->addSelect(DB::raw("NULL as option_name"));

            if (Schema::hasColumn('reviews', 'reference_code')) {
                $reviewsQuery->addSelect('r.reference_code');
            } else {
                $reviewsQuery->addSelect(DB::raw("NULL as reference_code"));
            }

            $reviewsQuery->addSelect(DB::raw("NULL as booking_date"));
        }

        // Optional customer phone
        if (Schema::hasColumn('customers', 'phone')) {
            $reviewsQuery->addSelect('c.phone as customer_phone');
        }

        // Customer profile image for Blade
        if (Schema::hasColumn('customers', 'profile_image')) {
            $reviewsQuery->addSelect(DB::raw("NULLIF(TRIM(IFNULL(c.profile_image, '')), '') as customer_profile_image"));
        } else {
            $reviewsQuery->addSelect(DB::raw("NULL as customer_profile_image"));
        }

        $reviews = $reviewsQuery->get();

        return view('provider.ratings', compact('ratingSummary', 'breakdown', 'reviews'));
    }

    private function bookingAreasSubquery()
    {
        if (!Schema::hasTable('booking_service_options') || !Schema::hasTable('service_options')) {
            return DB::table('bookings as b_fallback')
                ->selectRaw('NULL as booking_id, NULL as areas_label')
                ->whereRaw('1 = 0');
        }

        return DB::table('booking_service_options as bso')
            ->join('service_options as so2', 'so2.id', '=', 'bso.service_option_id')
            ->selectRaw("bso.booking_id, GROUP_CONCAT(so2.label ORDER BY so2.label SEPARATOR ', ') as areas_label")
            ->groupBy('bso.booking_id');
    }
}
