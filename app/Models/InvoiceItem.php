<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'vip_invoice_items';

    protected $fillable = [
        'invoice_id', 'description', 'qty', 'unit_price', 'total', 'sort_order',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
        'total' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
