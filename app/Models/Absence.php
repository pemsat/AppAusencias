<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Absence extends Model
{
    use HasFactory;

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
