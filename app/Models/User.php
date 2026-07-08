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
            'password'          => 'hashed',
        ];
    }

    public function initials(): string
    {
        if ($this->first_name && $this->last_name) {
            return Str::upper(Str::substr($this->first_name, 0, 1) . Str::substr($this->last_name, 0, 1));
        }
        return Str::of($this->name)->explode(' ')->map(fn(string $n) => Str::of($n)->substr(0, 1))->implode('');
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    /**
     * Super Admin — TahiConnect platform owner.
     * Manages system configuration, shop owner accounts, audit logs,
     * system monitoring. Does NOT manage tailoring business operations.
     */
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }

    /**
     * Shop Owner — Tailoring business owner.
     * Manages staff, orders, appointments, catalog, fabrics, payments,
     * business analytics, and business profile.
     */
    public function isShopOwner(): bool  { return $this->role === 'shop_owner'; }

    /**
     * Tailor Staff — Production team member.
     * Records measurements, processes orders, updates production stages,
     * manages appointments, performs fittings and validation.
     */
    public function isStaff(): bool      { return $this->role === 'tailor_staff'; }

    /**
     * Customer — End client of the tailoring business.
     * Books appointments, places orders, tracks orders,
     * uses virtual try-on, views payments.
     */
    public function isCustomer(): bool   { return $this->role === 'customer'; }

    /**
     * Legacy helper — kept for any backward-compat checks.
     * Maps to isSuperAdmin() in the new role model.
     */
    public function isAdmin(): bool      { return $this->isSuperAdmin(); }

    /**
     * Whether this user has any management/operational access to the shop.
     * Both the Shop Owner and Super Admin can access shop data.
     */
    public function managesShop(): bool  { return $this->isShopOwner() || $this->isSuperAdmin(); }

    // ── Shop relationship ─────────────────────────────────────────────────────
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function measurements()         { return $this->hasMany(Measurement::class); }
    public function appointments()         { return $this->hasMany(Appointment::class); }
    public function orders()               { return $this->hasMany(Order::class); }
    public function payments()             { return $this->hasMany(Payment::class); }
    public function customNotifications()  { return $this->hasMany(CustomNotification::class); }
    public function activityLogs()         { return $this->hasMany(ActivityLog::class); }
    public function assignedOrders()       { return $this->hasMany(Order::class, 'staff_id'); }
    public function assignedAppointments() { return $this->hasMany(Appointment::class, 'staff_id'); }
    public function shopReviews()          { return $this->hasMany(ShopReview::class); }
}
