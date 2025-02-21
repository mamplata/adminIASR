<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'department',
        'publisher',
        'type',
        'content',
    ];

    protected $casts = [
        'content' => 'array', // automatically decode the JSON into an array
    ];
}
