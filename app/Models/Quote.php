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
        return $this->belongsTo(VipUser::class, 'created_by');
    }
}
