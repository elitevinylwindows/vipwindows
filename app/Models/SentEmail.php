<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    protected $fillable = ['to', 'cc', 'subject', 'body', 'sent_by'];
}
