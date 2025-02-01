<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Matematicas',
            'Fisica',
            'Informatica',
            'Biología',
            'Química',
            'Inglés',
            'Historia',
            'Filosofía',
            'Educación Física',
            'Arte',
            'Música',
            'Religión',
            'Tecnología',
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['name' => $department]);
        }
    }
}
