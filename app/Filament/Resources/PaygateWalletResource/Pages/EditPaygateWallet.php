<?php

namespace App\Filament\Resources\PaygateWalletResource\Pages;

use App\Filament\Resources\PaygateWalletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaygateWallet extends EditRecord
{
    protected static string $resource = PaygateWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
