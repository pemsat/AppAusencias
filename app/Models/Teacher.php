<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Teacher extends Model
{
    use HasRoles;
    protected $fillable = [
        'name',
        'alias',
        'email',
        'password',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}
