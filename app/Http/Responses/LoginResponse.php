<?php

namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (auth()->user()->is_admin) {
            return redirect()->to(Dashboard::getUrl(panel: 'administradores'));
        }

        return parent::toResponse($request);
    }
}
