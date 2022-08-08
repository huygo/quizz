<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLogDetail extends Model
{
    use HasFactory;
    protected $table = 'account_log_detail';
    public $timestamps = false;
}
