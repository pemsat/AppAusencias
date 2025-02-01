<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = [
        'teacher_id',
        'date',
        'hour',
        'comment',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
