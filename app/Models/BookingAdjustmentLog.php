<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAdjustmentLog extends Model
{
    protected $fillable = [
        'booking_adjustment_id',
        'booking_id',
        'actor_role',
        'actor_id',
        'action',
        'note',
        'payload',
    ];
}
