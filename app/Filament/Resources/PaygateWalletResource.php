<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaygateWalletResource\Pages;
use App\Filament\Resources\PaygateWalletResource\RelationManagers;
use App\Models\PaygateWallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaygateWalletResource extends Resource
{
    protected static ?string $model = PaygateWallet::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Paygate Transactions';
    protected static ?string $pluralModelLabel = 'Paygate Transactions';
    protected static ?string $modelLabel = 'Paygate Transaction';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    ->disabled(),
                Forms\Components\TextInput::make('address_in')
                    ->label('Address In')
                    ->disabled(),
                Forms\Components\TextInput::make('polygon_address_in')
                    ->label('Polygon Address In')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                Forms\Components\TextInput::make('value_coin')
                    ->label('Amount (Coin)')
                    ->disabled(),
                Forms\Components\TextInput::make('coin')
                    ->label('Coin')
                    ->disabled(),
                Forms\Components\TextInput::make('txid_in')
                    ->label('TXID In')
                    ->disabled(),
                Forms\Components\TextInput::make('txid_out')
                    ->label('TXID Out')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('coin')
                    ->label('Coin'),
                Tables\Columns\TextColumn::make('value_coin')
                    ->label('Amount'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaygateWallets::route('/'),
            'create' => Pages\CreatePaygateWallet::route('/create'),
            'edit' => Pages\EditPaygateWallet::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
