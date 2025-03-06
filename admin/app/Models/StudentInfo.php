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
        'semester',
        'year',
        'image',
        'enrolled'
    ];

    // One student can have many registered cards
    public function registeredCards()
    {
        return $this->hasOne(RegisteredCard::class);
    }
}
