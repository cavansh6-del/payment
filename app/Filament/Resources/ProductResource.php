<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Image;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\NumberInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?string $modelLabel = 'Products';
    protected static ?string $pluralModelLabel = 'Products';
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('name')
                ->required()
                ->maxLength(255),
/*            Forms\Components\FileUpload::make('image_url')
                ->label('عکس')
                ->image()
                ->directory('mediaFiles')
                ->imagePreviewHeight('150')
                ->required(),*/
            RichEditor::make('description')
                ->label('description'),
            TextInput::make('price')
                ->label('price ($)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(','),
            TextInput::make('product_invoice')
                ->label('Product title in invoice')
                ->required()
                ->maxLength(255),
            Toggle::make('published')
                ->label('Is published ?')
                ->inline(false),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->sortable(),
        /*        ImageColumn::make('image_url')->label('عکس')->sortable(),*/
                TextColumn::make('description')->label('Description')
                    ->formatStateUsing(fn ($state) => strip_tags($state))
                    ->limit(100)
                    ->wrap()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state))
                    ->sortable(),
                ToggleColumn::make('published')
                    ->label('Is published ?')
                    ->sortable()
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role =='admin';
    }

}
