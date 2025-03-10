<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentInfo extends Model
{
    protected $table = 'student_infos';

    protected $fillable = [
        'studentId',
        'fName',
        'lName',
        'program',
        'department',
        'yearLevel',
        'image',
        'last_enrolled_at'
    ];

    // One student can have many registered cards
    public function registeredCards()
    {
        return $this->hasOne(RegisteredCard::class);
    }
}
