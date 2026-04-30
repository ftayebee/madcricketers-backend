<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'full_name',
        'slug',
        'nickname',
        'email',
        'blood_group',
        'password',
        'phone',
        'address',
        'role_id',
        'status',
        'image',
    ];

    protected $hidden = [
        'password',
        'visible_pass',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getPrimaryRoleAttribute()
    {
        return $this->roles->first() ?: $this->role;
    }

    public function getImageAttribute()
    {
        $originalImage = $this->getAttributes()['image'] ?? null;

        if ($this->hasRole('player')) {
            $folder = 'uploads/players/';
        } else {
            $folder = 'uploads/users/';
        }

        $imagePath = $folder . $originalImage;
        $defaultLogo = asset('storage/assets/images/users/dummy-avatar.jpg');

        if ($originalImage && Storage::exists('public/' . $imagePath)) {
            return asset('storage/' . $imagePath);
        }

        return $defaultLogo;
    }

    public function player(){
        return $this->hasOne(Player::class, 'user_id', 'id');
    }
}
