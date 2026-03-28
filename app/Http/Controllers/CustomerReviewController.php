<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerReviewController extends Controller
{
    public function index()
    {
        $customerId = session('user_id');

        $areasSub = $this->bookingAreasSubquery();

        $reviews = DB::table('bookings as b')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoin('reviews as r', 'r.booking_id', '=', 'b.id')
            ->join('service_providers as p', 'p.id', '=', 'b.provider_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->where('b.customer_id', $customerId)
            ->where('b.status', 'completed')
            ->select(
                'b.id as booking_id',
                'b.reference_code',
                'b.booking_date',
                'b.price',
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                DB::raw("TRIM(CONCAT(COALESCE(p.first_name,''), ' ', COALESCE(p.last_name,''))) as provider"),
                'r.rating',
                'r.comment',
                Schema::hasColumn('reviews', 'attachment_path')
                    ? 'r.attachment_path'
                    : DB::raw('NULL as attachment_path'),
                Schema::hasColumn('reviews', 'attachment_name')
                    ? 'r.attachment_name'
                    : DB::raw('NULL as attachment_name'),
                Schema::hasColumn('reviews', 'attachment_mime')
                    ? 'r.attachment_mime'
                    : DB::raw('NULL as attachment_mime')
            )
            ->orderByDesc('b.booking_date')
            ->orderByDesc('b.created_at')
            ->get();

        return view('customer.reviews.index', compact('reviews'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $customerId = (int) session('user_id');

        $booking = DB::table('bookings')
            ->where('id', $request->integer('booking_id'))
            ->where('customer_id', $customerId)
            ->where('status', 'completed')
            ->first(['id', 'provider_id']);

        if (!$booking) {
            return back()->withErrors([
                'review' => 'Only your completed bookings can be reviewed.',
            ]);
        }

        $existing = DB::table('reviews')
            ->where('booking_id', $booking->id)
            ->where('customer_id', $customerId)
            ->first([
                'id',
                Schema::hasColumn('reviews', 'attachment_path')
                    ? 'attachment_path'
                    : DB::raw('NULL as attachment_path'),
            ]);

        if ($existing && (int) $existing->id > 0) {
            return back()->withErrors([
                'review' => 'You already submitted a review for this booking.',
            ]);
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;

        if ($request->hasFile('attachment')) {
            try {
                $file = $request->file('attachment');
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = 'review_' . $customerId . '_' . Str::uuid() . '.' . $extension;
                $attachmentPath = str_replace('\\', '/', $file->storeAs('reviews/customer', $filename, 'public'));
                $attachmentName = $file->getClientOriginalName();
                $attachmentMime = $file->getClientMimeType();
            } catch (\Throwable $exception) {
                Log::error('Customer review attachment upload failed', [
                    'customer_id' => $customerId,
                    'booking_id' => $booking->id,
                    'message' => $exception->getMessage(),
                ]);

                return back()->withErrors([
                    'attachment' => 'Review attachment upload failed. Please try again.',
                ]);
            }
        }

        $payload = [
            'booking_id' => $booking->id,
            'customer_id' => $customerId,
            'provider_id' => $booking->provider_id,
            'rating' => $request->integer('rating'),
            'comment' => trim((string) $request->input('comment', '')) ?: null,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('reviews', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        if (Schema::hasColumn('reviews', 'attachment_path')) {
            $payload['attachment_path'] = $attachmentPath;
        }

        if (Schema::hasColumn('reviews', 'attachment_name')) {
            $payload['attachment_name'] = $attachmentName;
        }

        if (Schema::hasColumn('reviews', 'attachment_mime')) {
            $payload['attachment_mime'] = $attachmentMime;
        }

        DB::table('reviews')->insert($payload);

        return back()->with('success', 'Review submitted successfully.');
    }

    public function attachment($filename)
    {
        $path = $this->normalizeAttachmentPath($filename);

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
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

    private function normalizeAttachmentPath($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = str_replace('\\', '/', trim((string) $value));

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_PATH) ?: $value;
        }

        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'storage/')) {
            $value = substr($value, 8);
        }

        if (Str::startsWith($value, 'reviews/customer/')) {
            return $value;
        }

        return 'reviews/customer/' . basename($value);
    }
}
