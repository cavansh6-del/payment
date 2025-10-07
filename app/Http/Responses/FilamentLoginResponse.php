<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;

class FilamentLoginResponse implements BaseLoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        dd('fff');
        $redirectUrl = session('intended_url', filament()->getUrl());
        session()->forget('intended_url');

        return redirect()->to($redirectUrl);
    }
}
