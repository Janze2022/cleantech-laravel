<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference_code',
        'customer_id',
        'provider_id',
        'service_id',
        'service_option_id',
        'contact_phone',
        'address',
        'customer_latitude',
        'customer_longitude',
        'formatted_address',
        'house_type',
        'booking_date',
        'requested_start_time',
        'time_start',
        'time_end',
        'price',
        'status',
        'cancellation_reason',
        'cancelled_by_role',
        'adjustment_status',
    ];

    public function adjustment()
    {
        return $this->hasOne(BookingAdjustment::class);
    }

    public function customerRating()
    {
        return $this->hasOne(CustomerRating::class);
    }
}
