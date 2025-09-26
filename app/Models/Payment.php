<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['player_id', 'type', 'amount', 'payment_date', 'reference', 'tournament_id'];

    public function player(){
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    public function tournament(){
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }
}
