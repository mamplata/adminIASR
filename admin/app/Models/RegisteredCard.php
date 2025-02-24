<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisteredCard extends Model
{
    protected $table = 'registered_cards';

    protected $fillable = [
        'studentId',
        'uid',
    ];

    // Each card belongs to a student
    public function studentInfo()
    {
        return $this->belongsTo(StudentInfo::class);
    }
}
