<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'uid',
        'student_id',
        'time_type',
        'status',
        'failure_reason',
    ];
}
