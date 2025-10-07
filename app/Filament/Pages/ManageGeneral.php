<?php

namespace App\Filament\Pages;

namespace App\Filament\Pages;

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
class ManageGeneral extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static string $view = 'filament.pages.manage-settings';
    public  $paygate_usdc;  // مقدار پیش‌فرض
    public  $litepay_secret;  // مقدار پیش‌فرض
    public  $litepay_vendor;  // مقدار پیش‌فرض
    public  $template_with_link;
    public  $template_with_email;

    public function mount(): void
    {
        $settings = Settings::all();

        foreach ($settings as $setting) {
            $this->{$setting->name} = $setting->payload;
            $this->form->fill([
                $setting->name => $setting->payload,
            ]);
        }


    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('paygate_usdc')
                    ->label('Paygate USDC')
                    ->default(''), // مقدار پیش‌فرض
                TextInput::make('litepay_secret')
                    ->label('Litepay Secret')
                    ->default(''), // مقدار پیش‌فرض
                TextInput::make('litepay_vendor')
                    ->label('Litepay Vendor')
                    ->default(''), // مقدار پیش‌فرض

                Section::make('Email Template with Link')
                    ->schema([
                        RichEditor::make('template_with_link')
                            ->label('Template Email with Link')
                            ->helperText('Use #email for the email address associated with the payment gateway, #link for the payment link, #orderId for the unique Order ID, #amount for the total order amount, #product for the product name, and #payment-receipt for the clickable payment receipt link.')
                    ]),

                // بخش قالب ایمیل با ایمیل
                Section::make('Email Template with Email')
                    ->schema([
                        RichEditor::make('template_with_email')
                            ->label('Template Email with Email')
                            ->helperText('Use #email for the email address associated with the payment gateway, #link for the payment link, #orderId for the unique Order ID, #amount for the total order amount, #product for the product name, and #payment-receipt for the clickable payment receipt link.')

                    ]),
            ]);
    }


    public function save(): void
    {
        $data = $this->validate();

        // اطمینان از اینکه هیچ فیلدی null نیست
        Settings::query()->where('name', 'template_with_link')->update(
            ['payload' => $this->form->getState()['template_with_link']]
        );
        Settings::query()->where('name', 'template_with_email')->update(
            ['payload' => $this->form->getState()['template_with_email']]
        );
        Settings::query()->where('name', 'paygate_usdc')->update(
            ['payload' => $this->form->getState()['paygate_usdc']]
        );
        Settings::query()->where('name', 'litepay_secret')->update(['payload' => $this->form->getState()['litepay_secret']]);
        Settings::query()->where('name', 'litepay_vendor')->update(
            ['payload' => $this->form->getState()['litepay_vendor']]
        );


        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role =='admin';
    }
}
