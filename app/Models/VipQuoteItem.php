<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipQuoteItem extends Model
{
    protected $table = 'vip_quote_items';
    protected $guarded = ['id'];

    protected $casts = [
        'tempered_fields' => 'array',
        'shape_params' => 'array',
        'panel_dimensions' => 'array',
        'retrofit_bottom_only' => 'boolean',
        'no_logo_lock' => 'boolean',
        'double_lock' => 'boolean',
        'custom_lock_position' => 'boolean',
        'custom_vent_latch' => 'boolean',
        'knocked_down' => 'boolean',
    ];

    public function quote()
    {
        return $this->belongsTo(VipQuote::class, 'quote_id');
    }
}
