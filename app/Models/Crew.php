<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    protected $fillable = ['name', 'description', 'status'];

    public function members()
    {
        return $this->belongsToMany(VipUser::class, 'crew_members', 'crew_id', 'user_id')
                    ->withPivot('is_lead')
                    ->withTimestamps();
    }

    public function lead()
    {
        return $this->members()->wherePivot('is_lead', true)->first();
    }

    public function orders()
    {
        return $this->hasMany(InstallationOrder::class, 'crew_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
