<?php

namespace App\Filament\Resources\AbsenceResource\Pages;

use App\Filament\Resources\AbsenceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

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


}
