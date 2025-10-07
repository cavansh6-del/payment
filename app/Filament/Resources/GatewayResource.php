<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GatewayResource\Pages;
use App\Filament\Resources\GatewayResource\RelationManagers;
use App\Models\Gateway;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GatewayResource extends Resource
{
    protected static ?string $model = Gateway::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Manage';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->required(),
                Forms\Components\TextInput::make('link')->required(),
                Forms\Components\Select::make('email_template_type')
                    ->label('Email Template Type')
                    ->options([
                        'email' => 'Email',
                        'link' => 'Link',
                    ])
                    ->required(),
                //Forms\Components\TextInput::make('host')->required(),
                Forms\Components\TextInput::make('description')->label('Invoice description')
                    ->helperText('You can use #order for the order ID and #product for the product name in the description.')
                    ->required(),
                Forms\Components\TextInput::make('max_transactions')->required()->numeric(),
                Forms\Components\TextInput::make('amount_limit')->required()->numeric(),
                Forms\Components\FileUpload::make('logo_path')
                    ->label('logo')
                    ->image()
                    ->disk('public')
                    ->directory('mediaFiles') // مسیر ذخیره‌سازی داخل public/storage/articles
                    ->imagePreviewHeight('150')
                    ->required(),
        //        Forms\Components\TextInput::make('inactive_after_max_transactions')->nullable()->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('host'),
                Tables\Columns\TextColumn::make('max_transactions'),
                Tables\Columns\TextColumn::make('amount_limit')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state)),

                Tables\Columns\TextColumn::make('transactions_amount_since_update')
                    ->label('Transactions amount')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state)),
                Tables\Columns\TextColumn::make('transactions_count_since_update')
                    ->label('Transactions count')
                    ->formatStateUsing(fn ($state) => number_format($state)),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('Y-m-d H:i:s'))
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate') // نام دکمه
                    ->action(function ($record) {
                        $record->transactions_amount_since_update	 = 0;
                        $record->transactions_count_since_update = 0;
                        $record->deactivated_at = null;
                        $record->last_updated_at =  Carbon::now();
                        $record->save();
                    })
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Gateway::whereNotNull('deactivated_at')->count();

        return $count > 0
            ? "{$count}  deactive" // نمایش تعداد رکوردهای غیرفعال
            : null; // در غیر این صورت هیچ برچسبی نمایش داده نمی‌شود
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
            'index' => Pages\ListGateways::route('/'),
            'create' => Pages\CreateGateway::route('/create'),
            'edit' => Pages\EditGateway::route('/{record}/edit'),
        ];
    }
    public static function canAccess(): bool
    {
        return auth()->user()->role =='admin';
    }
}
