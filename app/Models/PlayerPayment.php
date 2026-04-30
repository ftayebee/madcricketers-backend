<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'due_id',
        'category_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'collected_by',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function due()
    {
        return $this->belongsTo(PlayerPaymentDue::class, 'due_id');
    }

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'category_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // ─── Observers ───────────────────────────────────────────────────

    public static function boot()
    {
        parent::boot();

        // After creating a payment, update the parent due's status
        static::created(function ($payment) {
            if ($payment->due_id) {
                optional($payment->due)->recalculate();
            }
        });

        // After deleting a payment, recalculate the parent due
        static::deleted(function ($payment) {
            if ($payment->due_id) {
                optional($payment->due)->recalculate();
            }
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function getPaymentMethodLabelAttribute()
    {
        $map = [
            'cash'          => 'Cash',
            'bkash'         => 'bKash',
            'nagad'         => 'Nagad',
            'bank_transfer' => 'Bank Transfer',
            'other'         => 'Other',
        ];
        return $map[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
