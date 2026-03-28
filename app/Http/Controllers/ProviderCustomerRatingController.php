<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProviderCustomerRatingController extends Controller
{
    private const EDIT_WINDOW_HOURS = 24;

    private function providerId(): int
    {
        $providerId = (int) session('provider_id');

        if (!$providerId) {
            abort(403, 'Provider session missing.');
        }

        return $providerId;
    }

    public function index()
    {
        $providerId = $this->providerId();
        $areasSub = $this->bookingAreasSubquery();

        $rows = DB::table('bookings as b')
            ->join('customers as c', 'c.id', '=', 'b.customer_id')
            ->join('services as s', 's.id', '=', 'b.service_id')
            ->leftJoin('service_options as o', 'o.id', '=', 'b.service_option_id')
            ->leftJoinSub($areasSub, 'areas', function ($join) {
                $join->on('areas.booking_id', '=', 'b.id');
            })
            ->leftJoin('customer_ratings as cr', 'cr.booking_id', '=', 'b.id')
            ->where('b.provider_id', $providerId)
            ->whereIn('b.status', ['completed', 'paid'])
            ->select(
                'b.id as booking_id',
                'b.reference_code',
                'b.booking_date',
                'b.price',
                'b.status as booking_status',
                'b.updated_at as booking_updated_at',
                'c.id as customer_id',
                'c.name as customer_name',
                'c.email as customer_email',
                Schema::hasColumn('customers', 'phone')
                    ? 'c.phone as customer_phone'
                    : DB::raw('NULL as customer_phone'),
                Schema::hasColumn('customers', 'profile_image')
                    ? 'c.profile_image as customer_profile_image'
                    : DB::raw('NULL as customer_profile_image'),
                's.name as service_name',
                DB::raw("COALESCE(areas.areas_label, o.label) as option_name"),
                'cr.id as rating_id',
                'cr.rating',
                'cr.booking_details_accurate',
                'cr.respectful',
                'cr.easy_to_communicate',
                'cr.paid_reliably',
                'cr.unexpected_extra_work',
                'cr.flag_understated_area',
                'cr.flag_hidden_sections',
                'cr.flag_misleading_request',
                'cr.flag_difficult_behavior',
                'cr.flag_payment_issue',
                'cr.flag_last_minute_changes',
                'cr.comment',
                'cr.attachment_path',
                'cr.attachment_name',
                'cr.attachment_mime',
                'cr.edit_count',
                'cr.editable_until',
                'cr.created_at as rating_created_at',
                'cr.updated_at as rating_updated_at'
            )
            ->orderByDesc('b.updated_at')
            ->orderByDesc('b.id')
            ->get()
            ->map(function ($row) {
                $editableUntil = !empty($row->editable_until)
                    ? Carbon::parse($row->editable_until)
                    : null;

                $row->can_edit = $row->rating_id
                    && $editableUntil
                    && now()->lt($editableUntil);

                return $row;
            });

        $pendingBookings = $rows->whereNull('rating_id')->values();
        $submittedRatings = $rows->whereNotNull('rating_id')->values();

        $summary = (object) [
            'completed_bookings' => $rows->count(),
            'pending_ratings' => $pendingBookings->count(),
            'submitted_ratings' => $submittedRatings->count(),
            'editable_ratings' => $submittedRatings->where('can_edit', true)->count(),
        ];

        return view('provider.customer_ratings.index', compact(
            'pendingBookings',
            'submittedRatings',
            'summary'
        ));
    }

    public function store(Request $request)
    {
        $providerId = $this->providerId();

        $request->validate([
            'booking_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'booking_details_accurate' => 'nullable|boolean',
            'respectful' => 'nullable|boolean',
            'easy_to_communicate' => 'nullable|boolean',
            'paid_reliably' => 'nullable|boolean',
            'unexpected_extra_work' => 'nullable|boolean',
            'flag_understated_area' => 'nullable|boolean',
            'flag_hidden_sections' => 'nullable|boolean',
            'flag_misleading_request' => 'nullable|boolean',
            'flag_difficult_behavior' => 'nullable|boolean',
            'flag_payment_issue' => 'nullable|boolean',
            'flag_last_minute_changes' => 'nullable|boolean',
            'comment' => 'nullable|string|max:1200',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $booking = DB::table('bookings')
            ->where('id', $request->integer('booking_id'))
            ->where('provider_id', $providerId)
            ->whereIn('status', ['completed', 'paid'])
            ->first(['id', 'customer_id', 'provider_id', 'reference_code']);

        if (!$booking) {
            return back()->withErrors([
                'customer_rating' => 'Only completed bookings can be rated.',
            ]);
        }

        if (DB::table('customer_ratings')->where('booking_id', $booking->id)->exists()) {
            return back()->withErrors([
                'customer_rating' => 'This booking already has a customer rating.',
            ]);
        }

        [$attachmentPath, $attachmentName, $attachmentMime] = $this->storeAttachment(
            $request,
            $providerId,
            $booking->id
        );

        $payload = $this->ratingPayload($request, $booking, $providerId, [
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'editable_until' => now()->addHours(self::EDIT_WINDOW_HOURS),
            'edit_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ratingId = DB::table('customer_ratings')->insertGetId($payload);

        $this->logRatingActivity(
            $ratingId,
            $booking->id,
            $booking->customer_id,
            $providerId,
            'created',
            $payload
        );

        return back()->with('success', 'Customer rating submitted.');
    }

    public function update(Request $request, int $id)
    {
        $providerId = $this->providerId();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'booking_details_accurate' => 'nullable|boolean',
            'respectful' => 'nullable|boolean',
            'easy_to_communicate' => 'nullable|boolean',
            'paid_reliably' => 'nullable|boolean',
            'unexpected_extra_work' => 'nullable|boolean',
            'flag_understated_area' => 'nullable|boolean',
            'flag_hidden_sections' => 'nullable|boolean',
            'flag_misleading_request' => 'nullable|boolean',
            'flag_difficult_behavior' => 'nullable|boolean',
            'flag_payment_issue' => 'nullable|boolean',
            'flag_last_minute_changes' => 'nullable|boolean',
            'comment' => 'nullable|string|max:1200',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $rating = DB::table('customer_ratings as cr')
            ->join('bookings as b', 'b.id', '=', 'cr.booking_id')
            ->where('cr.id', $id)
            ->where('cr.provider_id', $providerId)
            ->where('b.provider_id', $providerId)
            ->select('cr.*', 'b.reference_code')
            ->first();

        if (!$rating) {
            return back()->withErrors([
                'customer_rating' => 'Customer rating not found.',
            ]);
        }

        if (!$rating->editable_until || now()->gte(Carbon::parse($rating->editable_until))) {
            return back()->withErrors([
                'customer_rating' => 'The customer rating edit window has already expired.',
            ]);
        }

        [$attachmentPath, $attachmentName, $attachmentMime] = $this->storeAttachment(
            $request,
            $providerId,
            $rating->booking_id,
            $rating->attachment_path
        );

        $payload = $this->ratingPayload($request, $rating, $providerId, [
            'attachment_path' => $attachmentPath ?? $rating->attachment_path,
            'attachment_name' => $attachmentName ?? $rating->attachment_name,
            'attachment_mime' => $attachmentMime ?? $rating->attachment_mime,
            'edit_count' => (int) ($rating->edit_count ?? 0) + 1,
            'updated_at' => now(),
        ]);

        DB::table('customer_ratings')
            ->where('id', $rating->id)
            ->update($payload);

        $this->logRatingActivity(
            $rating->id,
            $rating->booking_id,
            $rating->customer_id,
            $providerId,
            'updated',
            $payload
        );

        return back()->with('success', 'Customer rating updated.');
    }

    public function attachment($filename)
    {
        $path = $this->normalizeAttachmentPath($filename);

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    private function ratingPayload(Request $request, object $booking, int $providerId, array $extra = []): array
    {
        return array_merge([
            'booking_id' => $booking->booking_id ?? $booking->id,
            'customer_id' => $booking->customer_id,
            'provider_id' => $providerId,
            'rating' => $request->integer('rating'),
            'booking_details_accurate' => $request->boolean('booking_details_accurate'),
            'respectful' => $request->boolean('respectful'),
            'easy_to_communicate' => $request->boolean('easy_to_communicate'),
            'paid_reliably' => $request->boolean('paid_reliably'),
            'unexpected_extra_work' => $request->boolean('unexpected_extra_work'),
            'flag_understated_area' => $request->boolean('flag_understated_area'),
            'flag_hidden_sections' => $request->boolean('flag_hidden_sections'),
            'flag_misleading_request' => $request->boolean('flag_misleading_request'),
            'flag_difficult_behavior' => $request->boolean('flag_difficult_behavior'),
            'flag_payment_issue' => $request->boolean('flag_payment_issue'),
            'flag_last_minute_changes' => $request->boolean('flag_last_minute_changes'),
            'comment' => trim((string) $request->input('comment', '')) ?: null,
        ], $extra);
    }

    private function storeAttachment(Request $request, int $providerId, int $bookingId, ?string $existingPath = null): array
    {
        if (!$request->hasFile('attachment')) {
            return [null, null, null];
        }

        try {
            $file = $request->file('attachment');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'customer_rating_' . $providerId . '_' . $bookingId . '_' . Str::uuid() . '.' . $extension;
            $path = str_replace('\\', '/', $file->storeAs('customer-ratings/provider', $filename, 'public'));

            if ($existingPath) {
                $normalizedOldPath = $this->normalizeAttachmentPath($existingPath);

                if ($normalizedOldPath && Storage::disk('public')->exists($normalizedOldPath)) {
                    Storage::disk('public')->delete($normalizedOldPath);
                }
            }

            return [
                $path,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
            ];
        } catch (\Throwable $exception) {
            Log::error('Provider customer rating attachment upload failed', [
                'provider_id' => $providerId,
                'booking_id' => $bookingId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function logRatingActivity(
        int $customerRatingId,
        int $bookingId,
        int $customerId,
        int $providerId,
        string $action,
        array $payload
    ): void {
        if (!Schema::hasTable('customer_rating_logs')) {
            return;
        }

        DB::table('customer_rating_logs')->insert([
            'customer_rating_id' => $customerRatingId,
            'booking_id' => $bookingId,
            'customer_id' => $customerId,
            'provider_id' => $providerId,
            'actor_role' => 'provider',
            'actor_id' => $providerId,
            'action' => $action,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

        if (Str::startsWith($value, 'customer-ratings/provider/')) {
            return $value;
        }

        return 'customer-ratings/provider/' . basename($value);
    }
}
