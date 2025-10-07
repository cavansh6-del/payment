<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// این فایل داخل public_html است
$linkPath   = __DIR__ . '/storage';                  // public_html/storage
$targetHint = __DIR__ . '/../storage/app/public';    // باید به این اشاره کند

echo "linkPath:  $linkPath\n";
echo "targetRaw: $targetHint\n";

// مسیر مقصد را واقعی کن
$targetReal = realpath($targetHint);
if ($targetReal === false) {
    echo "❌ realpath() نتوانست مقصد را پیدا کند: $targetHint\n";
    echo "➡️ چک کن که مسیر /storage/app/public وجود داشته باشد و دسترسی داشته باشی.\n";
    exit(1);
}
echo "targetReal: $targetReal\n";

// اگر چیزی با همین نام هست، وضعیتش را مشخص کن
if (file_exists($linkPath) || is_link($linkPath)) {
    if (is_link($linkPath)) {
        $current = readlink($linkPath);
        echo "ℹ️ موجود است (symlink): points to => $current\n";
        // اگر به جای اشتباه اشاره می‌کند، حذفش کن
        if ($current !== $targetReal) {
            echo "⚠️ لینک به مسیر دیگری اشاره می‌کند. حذف و بازسازی...\n";
            if (!unlink($linkPath)) {
                echo "❌ نتوانستم symlink قدیمی را حذف کنم.\n";
                exit(1);
            }
        } else {
            echo "✅ لینک درست است. یک تست می‌کنیم...\n";
        }
    } else {
        // فولدر/فایل واقعی است؛ حذفش نکنیم که دیتا از دست نرود.
        echo "⚠️ '$linkPath' یک پوشه/فایل واقعی است، نه symlink.\n";
        echo "اگر قصد لینک‌کردن داری آن را موقتاً تغییرنام بده (مثلاً storage_old) و دوباره اسکریپت را اجرا کن.\n";
        exit(1);
    }
}

// اگر در این مرحله لینک وجود ندارد، بساز
if (!is_link($linkPath)) {
    echo "⛓ تلاش برای ساخت symlink...\n";
    $ok = @symlink($targetReal, $linkPath);
    if (!$ok) {
        echo "❌ symlink() شکست خورد. احتمالاً روی هاست غیرفعال است یا محدودیت مالک/امنیتی دارد.\n";
    } else {
        echo "✅ Symlink ساخته شد: $linkPath -> $targetReal\n";
    }
}

// راستی‌آزمایی لینک (اگر ساخته شده باشد)
if (is_link($linkPath)) {
    $p = readlink($linkPath);
    echo "🔎 readlink: $p\n";
    // بررسی دسترسی به یک مسیر نمونه زیر لینک
    if (is_dir($linkPath) && is_readable($linkPath)) {
        echo "✅ به نظر می‌رسد لینک از نظر فایل‌سیستمی سالم است.\n";
        echo "📎 اگر در مرورگر 404 می‌بینی، مشکل از تنظیمات وب‌سرور برای دنبال‌کردن symlink است.\n";
        echo "— Apache: داخل public/.htaccess (یا روت دامنه) باید یکی از این‌ها فعال باشد:\n";
        echo "    Options +FollowSymLinks\n";
        echo "    یا Options +SymLinksIfOwnerMatch\n";
        echo "  توجه: بعضی هاست‌ها اجازه‌ی تغییر Options نمی‌دهند.\n";
        echo "— Nginx: directiveهای مثل disable_symlinks باید اجازه بدهند.\n";
        exit(0);
    } else {
        echo "⚠️ لینک هست ولی ظاهراً خواندنی/قابل دسترسی نیست.\n";
        // ادامه می‌دهیم به fallback
    }
}

// Fallback: کپی کردن محتوا (وقتی symlink کار نمی‌کند)
echo "🔁 Fallback: کپی کردن محتوای $targetReal به $linkPath ...\n";
if (!file_exists($linkPath)) {
    if (!mkdir($linkPath, 0775, true)) {
        echo "❌ ساخت پوشه مقصد برای کپی ناموفق بود.\n";
        exit(1);
    }
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetReal, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$errors = 0;
foreach ($it as $item) {
    $dest = $linkPath . DIRECTORY_SEPARATOR . $it->getSubPathName();
    if ($item->isDir()) {
        if (!is_dir($dest) && !mkdir($dest, 0775, true)) {
            echo "❌ mkdir failed: $dest\n";
            $errors++;
        }
    } else {
        if (!@copy($item->getPathname(), $dest)) {
            echo "❌ copy failed: {$item->getPathname()} -> $dest\n";
            $errors++;
        }
    }
}

if ($errors === 0) {
    echo "✅ کپی کامل شد. چون symlink مجاز نبود، فعلاً با نسخه‌ی کپی‌شده کار کن.\n";
    echo "🧹 اگر بعداً دسترسی symlink درست شد، این پوشه را حذف کن و دوباره symlink بساز تا استاندارد لاراول شود.\n";
} else {
    echo "⚠️ کپی با $errors خطا انجام شد؛ لاگ بالا را ببین.\n";
}