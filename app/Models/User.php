<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password',
        'contact_number', 'role', 'avatar', 'address', 'shop_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function initials(): string
    {
        if ($this->first_name && $this->last_name) {
            return Str::upper(Str::substr($this->first_name, 0, 1) . Str::substr($this->last_name, 0, 1));
        }
        return Str::of($this->name)->explode(' ')->map(fn(string $n) => Str::of($n)->substr(0, 1))->implode('');
    }

    // Role helpers
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isStaff(): bool { return $this->role === 'tailor_staff'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isShopOwner(): bool { return $this->role === 'shop_owner'; }

    // Shop relationship
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * The shop this user owns (shop_owner role).
     * Shop owners find their shop via User records with shop_id, but
     * they can also be looked up by checking staff/members.
     */
    public function ownedShops()
    {
        return Shop::whereIn('id',
            User::where('id', $this->id)->where('role', 'shop_owner')->pluck('shop_id')
        );
    }

    // Relationships
    public function measurements() { return $this->hasMany(Measurement::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function customNotifications() { return $this->hasMany(CustomNotification::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function assignedOrders() { return $this->hasMany(Order::class, 'staff_id'); }
    public function assignedAppointments() { return $this->hasMany(Appointment::class, 'staff_id'); }
    public function shopReviews() { return $this->hasMany(ShopReview::class); }
}
