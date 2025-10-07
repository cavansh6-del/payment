<?php

namespace App\Filament\Resources;

use App\Filament\Pages\OrderPaymentView;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Firebase\JWT\JWT;
use Carbon\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state) => '$'.number_format($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')->sortable(),
                Tables\Columns\TextColumn::make('gateway.name')
                    ->label('Gateway')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed'  => 'success',   // سبز
                        'processing' => 'warning',   // زرد
                        'cancelled', 'failed' => 'danger',    // قرمز
                        default      => 'secondary', // خاکستری
                    }),
                Tables\Columns\TextColumn::make('paypal_order_id')
                    ->label('Paypal id')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Add the custom "Show Invoice" button
                Tables\Actions\Action::make('showInvoice')
                    ->label('Show Invoice') // Button label in English
                    ->url(fn (Order $record) => self::generateInvoiceLink($record)) // Generate JWT and create the link
                    ->icon('heroicon-o-document-text') // Icon for the button
                    ->color('primary') // Button color
                    ->visible(fn (Order $record) => $record->gateway_id)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('addPaymentReceipt')
                    ->label('Add Payment Receipt') // Button label in English
                    ->url(fn (Order $record) => route('filament.admin.pages.order-payment', ['order_id' => $record->id]))
                    ->icon('heroicon-o-document-text') // Icon for the button
                    ->color('primary') // Button color
                    ->visible(fn (Order $record) => in_array($record->status,['pending','processing']))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('paymentReceipt')
                    ->label('Payment Receipt') // Button label in English
                    ->url(fn (Order $record) => route('filament.admin.pages.order-payment-view', ['order_id' => $record->id]))
                    ->icon('heroicon-o-document-text') // Icon for the button
                    ->color('primary') // Button color
                    ->visible(fn (Order $record) => $record->latestPayment  && auth()->user()->role == 'admin')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }




    public static function generateInvoiceLink(Order $order)
    {
        // Secret key for signing JWT
        $secretKey = env('JWT_SECRET_KEY'); // You can store this key in `.env`

        // Data to be included in JWT
        $payload = [
            'order_id' => $order->id,
            'expires_at' => Carbon::now()->addDay()->timestamp, // Expiry date (24 hours)
            'iat' => Carbon::now()->timestamp, // Token issue time
        ];

        // Generate JWT token (Add the algorithm as the third parameter)
        $jwt = JWT::encode($payload, $secretKey, 'HS256');  // 'HS256' is the default algorithm, but you should explicitly specify it.

        // Create the URL with JWT token
        return route('order.invoice', ['token' => $jwt]);
    }

    public static function getNavigationBadge(): string
    {
        $processingOrdersCount = Order::where('status', 'processing')->count();

        return $processingOrdersCount ? (string) $processingOrdersCount : '';
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // اگر کاربر Admin است، تمام سفارشات را نشان بده
        if (auth()->user()->role !='admin') {
            // در غیر این صورت فقط سفارشات مربوط به کاربر فعلی را نشان بده
            $query->where('user_id', auth()->user()->id);
        }

        return $query;
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
