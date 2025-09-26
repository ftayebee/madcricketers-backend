<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyDonation extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'year', 'month', 'expected_amount', 'paid_amount', 'is_paid'];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
