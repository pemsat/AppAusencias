<?php

namespace App\Filament\Auth;

use App\Models\Teacher;
use Filament\Pages\Auth\Register;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TeacherRegister extends Register
{
    /**
     * Create a new class instance.
     */
    protected function handleRegistration(array $data): Model
    {
        $user = $this->getTeacherModel()::create($data);
        $teacherRole = Role::create(['name' => 'teacher']);


        $createAbsence = Permission::create(['name' => 'create absences']);
        $editAbsence = Permission::create(['name' => 'edit absences']);
        $deleteAbsence = Permission::create(['name' => 'delete absences']);
        $viewAbsence = Permission::create(['name' => 'view absences']);


       $teacherRole->givePermissionTo([$createAbsence, $editAbsence, $deleteAbsence, $viewAbsence]);
        $user->assignRole('teacher');

        return $user;
    }
}
