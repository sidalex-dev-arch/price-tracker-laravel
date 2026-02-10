<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackLink extends Model
{
    use HasFactory;

    protected $fillable = ["store_name", "url", "last_price", "last_checked_at"];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function history()
    {
        return $this->hasMany(PriceHistory::class);
    }
}