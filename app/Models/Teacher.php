<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Teacher extends Model implements FilamentUser
{
    use HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return false;
    }

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
