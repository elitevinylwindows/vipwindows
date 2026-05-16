<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'vip_invoices';

    protected $fillable = [
        'invoice_number', 'quote_id', 'customer_name', 'customer_email',
        'customer_phone', 'customer_address', 'billing_address', 'status',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'balance_due', 'due_date', 'paid_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
        'amount_paid' => 'float',
        'balance_due' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function creator()
    {
        return $this->belongsTo(VipUser::class, 'created_by');
    }
}
