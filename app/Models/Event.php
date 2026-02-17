<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'event_date',
        'total_tickets',
        'available_tickets',
        'version',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
