<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'url',
        'last_price',
        'last_checked_at',
        'alert_triggered_at',           // новое поле
        'alert_percentage_detected',    // новое поле
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'alert_triggered_at' => 'datetime',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function history()
    {
        return $this->hasMany(PriceHistory::class);
    }
}