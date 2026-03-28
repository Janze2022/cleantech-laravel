<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRatingLog extends Model
{
    protected $fillable = [
        'customer_rating_id',
        'booking_id',
        'customer_id',
        'provider_id',
        'actor_role',
        'actor_id',
        'action',
        'payload',
    ];
}
