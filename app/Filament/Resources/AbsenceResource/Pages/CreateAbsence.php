<?php

namespace App\Filament\Resources\AbsenceResource\Pages;

use App\Filament\Resources\AbsenceResource;
use App\Mail\AbsenceCreated;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateAbsence extends CreateRecord
{
    protected static string $resource = AbsenceResource::class;

    // protected function getRedirectUrl(): string
    // {
    //     return route('filament.administradores.pages.dashboard');
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return [...$data, 'user_id' => Auth::id()];
    }

    protected function afterCreate(): void
{
    if (!$this->record) {
        throw new \Exception('No absence record found when sending email.');
    }
    
    $adminEmail = 'admin@example.com';

    Mail::to($adminEmail)->send(new AbsenceCreated($this->record));
}
}
