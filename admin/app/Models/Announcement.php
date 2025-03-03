<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'departments',
        'publisher',
        'type',
        'content',
        'publication_date',
    ];

    protected $casts = [
        'content' => 'array',
        'publication_date' => 'date',
    ];
}
