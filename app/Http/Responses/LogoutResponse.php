<?php

namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Filament\Http\Responses\Auth\LoginResponse as BaseLogoutResponse;
use Filament\Facades\Filament;

class LogoutResponse extends BaseLogoutResponse
{

    public function toResponse($request): RedirectResponse
    {
        if(Filament::getCurrentPanel()->getId() === 'admin'){
            return redirect()->to(Filament::getLoginUrl());
        }
        return parent::toResponse($request);
    }
}
