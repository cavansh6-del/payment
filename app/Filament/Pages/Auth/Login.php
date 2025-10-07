<?php


namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\FilamentManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\Login as BaseAuth;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Models\Contracts\FilamentUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Component;
use Illuminate\Validation\ValidationException;


class Login extends BaseAuth
{

    public function mount(): void
    {

        $token = request()->input('token'); // دریافت توکن از URL


        // اگر توکن موجود باشد، عملیات لاگین را از طریق SSO انجام می‌دهیم
        if ($token) {
            $orderId = $this->authenticateWithToken($token);
            if (Filament::auth()->check()) {
                redirect()->intended(route('filament.admin.pages.order-payment', ['order_id' => $orderId]));
            }
        } else {
            parent::mount(); // در صورتی که توکن موجود نبود، به متد mount اصلی برو
        }

    }

    public function getTitle(): string | Htmlable
    {
        return '';
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL ) ? 'email' : 'name';

        return [
            $login_type => $data['login'],
            'password'  => $data['password'],
        ];
    }


    public function authenticate(): LoginResponse
    {
        $this->validate();
        $data = $this->form->getState();

        if (!Auth::attempt([
            'email' => $this->form->getState()['login'],
            'password' => $this->form->getState()['password'],
        ])) {

            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        if($user->locked == true){
            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        $hasGroup =in_array( auth()->user()?->role, ['adminsupport', 'administrator', 'editor', 'editor', 'admin']);

        if(!$hasGroup){
            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function authenticateWithToken(string $token)
    {
        $secretKey = env('JWT_SECRET_KEY');

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $user = User::find($decoded->user_id);

             // اگر توکن معتبر باشد، کاربر را لاگین می‌کنیم
            if ($user) {
                Auth::login($user);
                // بررسی دسترسی به پنل برای کاربر
                if ($user instanceof FilamentUser && !$user->canAccessPanel(Filament::getCurrentPanel())) {
                    Filament::auth()->logout();
                    throw ValidationException::withMessages([
                        'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
                    ]);
                }


                session()->regenerate();

                return $decoded->order_id;

            } else {
                throw ValidationException::withMessages([
                    'data.login' => __('Invalid token'),
                ]);
            }
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }
    }


}
