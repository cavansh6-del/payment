<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $paygate_usdc;
    public string $litepay_secret;
    public string $litepay_vendor;

    public static function group(): string
    {
        return 'general';
    }
}