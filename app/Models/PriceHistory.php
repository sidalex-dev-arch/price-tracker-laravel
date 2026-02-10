<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = ["price", "raw_price", "checked_at"];

    public function trackLink()
    {
        return $this->belongsTo(TrackLink::class);
    }
}