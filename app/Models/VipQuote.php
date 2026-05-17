<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VipQuote extends Model
{
    use SoftDeletes;

    protected $table = 'vip_quotes';
    protected $guarded = ['id'];

    protected $casts = [
        'entry_date' => 'date',
        'expected_delivery' => 'date',
        'valid_until' => 'date',
        'is_special_order' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(VipQuoteItem::class, 'quote_id');
    }

    public function installer()
    {
        return $this->belongsTo(VipUser::class, 'installer_id');
    }

    public function scopeEnteredBy($query, string $name)
    {
        return $query->where('entered_by', $name);
    }
}
