<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerPaymentDue extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'category_id',
        'amount',
        'due_date',
        'period_label',
        'notes',
        'status',
        'paid_amount',
        'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'category_id');
    }

    public function payments()
    {
        return $this->hasMany(PlayerPayment::class, 'due_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['pending', 'partial'])
                     ->where('due_date', '<', now()->toDateString());
    }

    // ─── Computed ────────────────────────────────────────────────────

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->due_date) return false;
        return in_array($this->status, ['pending', 'partial'])
            && $this->due_date->isPast();
    }

    /**
     * Recalculate paid_amount from payments and update status.
     * Call this after every PlayerPayment insert/delete.
     */
    public function recalculate()
    {
        $paid = $this->payments()->sum('amount');
        $this->paid_amount = $paid;

        if ($paid <= 0) {
            $this->status = 'pending';
        } elseif ($paid >= $this->amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }
}
