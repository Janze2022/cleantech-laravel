<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAdjustment extends Model
{
    protected $fillable = [
        'booking_id',
        'provider_id',
        'customer_id',
        'original_service_name',
        'original_option_summary',
        'original_price',
        'proposed_service_name',
        'proposed_scope_summary',
        'additional_fee',
        'proposed_total',
        'price_increase_percent',
        'reason_payload',
        'other_reason',
        'provider_note',
        'customer_response_note',
        'evidence_path',
        'evidence_name',
        'evidence_mime',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'original_price' => 'float',
        'additional_fee' => 'float',
        'proposed_total' => 'float',
        'price_increase_percent' => 'float',
        'resolved_at' => 'datetime',
    ];
}
