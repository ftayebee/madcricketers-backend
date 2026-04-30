<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'recurrence_type',
        'default_amount',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'default_amount' => 'decimal:2',
        'sort_order'     => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function dues()
    {
        return $this->hasMany(PlayerPaymentDue::class, 'category_id');
    }

    public function payments()
    {
        return $this->hasMany(PlayerPayment::class, 'category_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function getRecurrenceLabelAttribute()
    {
        $map = [
            'monthly'  => 'Monthly',
            'annual'   => 'Annual',
            'one_time' => 'One-time',
        ];
        return $map[$this->recurrence_type] ?? ucfirst($this->recurrence_type);
    }

    /**
     * Auto-generate slug from name if not provided.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = static::uniqueSlug($category->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
