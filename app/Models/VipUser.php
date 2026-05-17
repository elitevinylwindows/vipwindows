<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class VipUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'vip_users';

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'notes',
        'role', 'customer_type', 'password', 'status',
        'company_name', 'company_logo_dark', 'company_logo_light', 'company_phone', 'company_fax',
        'company_email', 'company_website', 'company_address', 'company_city', 'company_state', 'company_zip',
        'price_markup_pct', 'price_markup_flat', 'booking_slug',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'technician']);
    }

    public function isInstaller(): bool
    {
        return $this->role === 'installer';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'technician', 'installer']);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'vip_installer_services', 'installer_id', 'service_id')
            ->withPivot('custom_price')
            ->withTimestamps();
    }

    public function installerServices()
    {
        return $this->hasMany(InstallerService::class, 'installer_id');
    }

    public function availability()
    {
        return $this->hasMany(InstallerAvailability::class, 'installer_id');
    }

    public function bookings()
    {
        return $this->hasMany(InstallerBooking::class, 'installer_id');
    }
}
