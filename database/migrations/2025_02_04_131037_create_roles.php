<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $teacherRole = Role::create(['name' => 'teacher']);


        $createAbsence = Permission::create(['name' => 'create absences']);
        $editAbsence = Permission::create(['name' => 'edit absences']);
        $deleteAbsence = Permission::create(['name' => 'delete absences']);
        $viewAbsence = Permission::create(['name' => 'view absences']);


       $teacherRole->givePermissionTo([$createAbsence, $editAbsence, $deleteAbsence, $viewAbsence]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
