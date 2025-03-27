<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnauthorizedLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'uid',
        'time_type',
        'reason',
    ];
}
