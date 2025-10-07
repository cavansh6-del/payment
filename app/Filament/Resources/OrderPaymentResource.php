<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderPaymentResource\Pages;
use App\Filament\Resources\OrderPaymentResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderPaymentResource extends Resource
{
    protected static ?string $model = OrderPayment::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'PayPal Transactions';
    protected static ?string $pluralModelLabel = 'PayPal Transactions';
    protected static ?string $modelLabel = 'PayPal Transaction';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->disabled(),
                Forms\Components\TextInput::make('payer_name')
                    ->label('Payer Name')
                    ->disabled(),
                Forms\Components\TextInput::make('payer_bank_info')
                    ->label('Payer Bank Info')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled(),
                Forms\Components\FileUpload::make('receipt_path')
                    ->label('Receipt File')
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
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('usd'), // فرض کردم USD
                Tables\Columns\TextColumn::make('payer_name')
                    ->label('Payer Name'),
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
                Tables\Actions\Action::make('addPaymentReceipt')
                    ->label('Add Payment Receipt') // Button label in English
                    ->url(fn (OrderPayment $record) => route('filament.admin.pages.order-payment', ['order_id' => $record->order->id]))
                    ->icon('heroicon-o-document-text') // Icon for the button
                    ->color('primary') // Button color
                    ->visible(fn (OrderPayment $record) => in_array($record->order->status,['pending','processing']))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('paymentReceipt')
                    ->label('Payment Receipt') // Button label in English
                    ->url(fn (OrderPayment $record) => route('filament.admin.pages.order-payment-view', ['order_id' => $record->order->id]))
                    ->icon('heroicon-o-document-text') // Icon for the button
                    ->color('primary') // Button color
                    ->visible(fn (OrderPayment $record) =>  auth()->user()->role == 'admin')
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListOrderPayments::route('/'),
            'create' => Pages\CreateOrderPayment::route('/create'),
            'edit' => Pages\EditOrderPayment::route('/{record}/edit'),
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
