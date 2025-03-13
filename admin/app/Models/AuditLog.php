<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'admin_name',
        'action',
        'type',
        'type_id',
        'details'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
