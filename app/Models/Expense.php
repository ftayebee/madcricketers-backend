<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'amount',
        'expense_date',
        'paid_by',
        'receipt_reference',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function getCategoryLabelAttribute()
    {
        $map = [
            'ground_rent'  => 'Ground Rent',
            'equipment'    => 'Equipment',
            'refreshment'  => 'Refreshment',
            'prize_money'  => 'Prize Money',
            'jersey'       => 'Jersey',
            'transport'    => 'Transport',
            'other'        => 'Other',
        ];
        return $map[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public static function categoryOptions()
    {
        return [
            'ground_rent' => 'Ground Rent',
            'equipment'   => 'Equipment',
            'refreshment' => 'Refreshment',
            'prize_money' => 'Prize Money',
            'jersey'      => 'Jersey',
            'transport'   => 'Transport',
            'other'       => 'Other',
        ];
    }
}
