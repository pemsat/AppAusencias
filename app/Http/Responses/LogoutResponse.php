<?php

namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;
use Filament\Facades\Filament;

class LogoutResponse extends BaseLogoutResponse
{

    public function toResponse($request): RedirectResponse
    {
        if(Filament::getCurrentPanel()->getId() === 'admin'){
            return redirect()->to(Filament::getLoginUrl());
        }
        return redirect()->to('/user/login');
    }
}
