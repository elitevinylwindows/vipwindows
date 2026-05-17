<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $table = 'elitevw_sales_quotes';
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    /**
     * Scope: quotes entered by a given user name.
     */
    public function scopeEnteredBy($query, string $name)
    {
        return $query->where('entered_by', $name);
    }
}
