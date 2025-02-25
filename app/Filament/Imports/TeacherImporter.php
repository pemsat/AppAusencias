<?php

namespace App\Filament\Imports;

use App\Models\Department;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->label('Correo')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255', 'unique:users,email']),
            ImportColumn::make('password')
                ->label('Contraseña')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('department_id')
                ->label('Departamento')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('alias')
                ->requiredMapping()
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?User
    {
        if (!Department::find($this->data['department_id'])) {
            Log::error('Department ID not found: ' . $this->data['department_id']);
            return null;
        }

        return User::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'name' => $this->data['name'],
            'alias' => $this->data['alias']?? null,
            'email' => $this->data['email'],
            'password' => Hash::make($this->data['password']),
            'department_id' => $this->data['department_id'],
        ]);

        //return new Teacher();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your teacher import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
