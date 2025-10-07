<?php

namespace App\Filament\Pages;

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Settings;
use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;

class OrderPayment extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-settings';
    protected static bool $shouldRegisterNavigation = false;
    public  $order_id;  // مقدار پیش‌فرض
    public  $payer_name;  // مقدار پیش‌فرض
    public  $payer_bank_info;  // مقدار پیش‌فرض
    public  $receipt_path = null;
    public  $template_with_email = null;

    public function mount(): void
    {


        $this->order_id = request()->query('order_id');

        $query = Order::query();
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        $order = $query->findOrFail($this->order_id);

        if (!$this->order_id || !$order) {
            abort(404, 'Order not found');
        }

        $orderPayment = \App\Models\OrderPayment::where('order_id', $this->order_id)->first();

        if ($orderPayment && $orderPayment->status != 'pending') {
            abort(404, 'Order not found');
        }
        if ($orderPayment) {
            $this->payer_name = $orderPayment->payer_name;
            $this->payer_bank_info = $orderPayment->payer_bank_info;
            $this->receipt_path = $orderPayment->receipt_path;

        }

        $this->form->fill([
            'receipt_path' => $this->receipt_path,
        ]);
/*
        $this->form->fill([
            $setting->name => $setting->payload,
        ]);*/


    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('order_id')
                    ->default($this->order_id)
                    ->required(),

                Forms\Components\TextInput::make('payer_name')
                    ->default($this->payer_name)
                    ->label('Payer Name'),

                Forms\Components\TextInput::make('payer_bank_info')
                    ->default($this->payer_bank_info)
                    ->label('Bank Info'),

                Forms\Components\FileUpload::make('receipt_path')
                    ->default($this->receipt_path)
                    ->label('Logo')
                    ->image()
                    ->directory('mediaFiles') //public/storage/articles
                    ->imagePreviewHeight('150')
                    ->required(),


            ]);
    }


    public function save(): void
    {
        $data = $this->validate();


        // ذخیره یا بروزرسانی اطلاعات در جدول OrderPayment
        \App\Models\OrderPayment::updateOrCreate(
            ['order_id' => $this->order_id],  // شرط برای پیدا کردن رکورد یا ایجاد جدید
            [
                'payer_name' => $this->form->getState()['payer_name'],
                'payer_bank_info' => $this->form->getState()['payer_bank_info'],
                'receipt_path' => $this->form->getState()['receipt_path'],
                'status' => 'pending',
            ]
        );

        $query = Order::query();
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        $order = $query->findOrFail($this->order_id);

        $order->status = 'processing';
        $order->save();

        Notification::make()
            ->title('Payment saved successfully.')
            ->success()
            ->send();

    }
}
