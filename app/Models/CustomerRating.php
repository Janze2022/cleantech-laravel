<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRating extends Model
{
    protected $fillable = [
        'booking_id',
        'customer_id',
        'provider_id',
        'rating',
        'booking_details_accurate',
        'respectful',
        'easy_to_communicate',
        'paid_reliably',
        'unexpected_extra_work',
        'flag_understated_area',
        'flag_hidden_sections',
        'flag_misleading_request',
        'flag_difficult_behavior',
        'flag_payment_issue',
        'flag_last_minute_changes',
        'comment',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'edit_count',
        'editable_until',
    ];

    protected $casts = [
        'booking_details_accurate' => 'bool',
        'respectful' => 'bool',
        'easy_to_communicate' => 'bool',
        'paid_reliably' => 'bool',
        'unexpected_extra_work' => 'bool',
        'flag_understated_area' => 'bool',
        'flag_hidden_sections' => 'bool',
        'flag_misleading_request' => 'bool',
        'flag_difficult_behavior' => 'bool',
        'flag_payment_issue' => 'bool',
        'flag_last_minute_changes' => 'bool',
        'rating' => 'int',
        'edit_count' => 'int',
        'editable_until' => 'datetime',
    ];
}
