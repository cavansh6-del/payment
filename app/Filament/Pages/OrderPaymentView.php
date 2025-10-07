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
use Illuminate\Support\Facades\Redirect;

class OrderPaymentView extends Page
{
    protected static string $view = 'filament.pages.order-payment';  // مسیر View جدید شما

    public $order_id;
    public $order;
    public $orderPayment;

    public function mount(): void
    {
        $this->order_id = request()->query('order_id');
        $this->order = Order::find($this->order_id);

        // اگر سفارش یافت نشد، خطای 404 می‌دهیم
        if (!$this->order ||  auth()->user()->role != 'admin') {
            abort(404, 'Order not found');
        }

        // دریافت اطلاعات پرداخت برای سفارش
        $this->orderPayment = \App\Models\OrderPayment::where('order_id', $this->order_id)->first();
    }

    public function updateOrderStatus($status)
    {
        $order = $this->order;

        // بررسی وضعیت صحیح
        if (!in_array($status, ['completed', 'cancelled'])) {
            abort(400, 'Invalid status');
        }

        // تغییر وضعیت سفارش
        $order->status = $status;
        $order->save();

        // نمایش پیام موفقیت
        Notification::make()
            ->title('Order status updated successfully!')
            ->success()
            ->send();

        // بازگشت به صفحه بعد از تغییر وضعیت
        return Redirect::route('filament.admin.resources.orders.index');
    }

}
