<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
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
        'email',
        'password',
        'phone',
        'address',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getImageAttribute()
    {
        $originalImage = $this->getAttributes()['image'] ?? null;

        $imagePath = 'public/uploads/users/' . $originalImage;
        $defaultLogo = asset('storage/assets/images/users/dummy-avatar.jpg');

        if ($originalImage && Storage::exists($imagePath)) {
            return asset('storage/uploads/users/' . $originalImage);
        }

        return $defaultLogo;
    }

    public function player(){
        return $this->hasOne(Player::class, 'user_id', 'id');
    }
}
