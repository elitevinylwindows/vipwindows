<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class VipUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'vip_users';

    protected $fillable = ['name', 'email', 'phone', 'role', 'password', 'status'];

    protected $hidden = ['password', 'remember_token'];

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'technician']);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}
