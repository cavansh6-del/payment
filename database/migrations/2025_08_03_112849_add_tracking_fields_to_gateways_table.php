<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            $table->boolean('is_active')
                ->default(true)
                ->after('inactive_after_max_transactions')
                ->comment('فعال بودن یا نبودن درگاه');

            $table->timestamp('last_updated_at')
                ->nullable()
                ->after('is_active')
                ->comment('تاریخ آخرین بروزرسانی یا برداشت پول از درگاه');

            $table->timestamp('deactivated_at')
                ->nullable()
                ->after('last_updated_at')
                ->comment('تاریخ غیرفعال شدن درگاه به دلیل رسیدن به محدودیت');

            $table->decimal('transactions_amount_since_update', 20, 2)
                ->default(0)
                ->after('deactivated_at')
                ->comment('مبلغ تراکنش‌های انجام شده از آخرین بروزرسانی درگاه');

            $table->decimal('total_transactions_amount', 20, 2)
                ->default(0)
                ->after('transactions_amount_since_update')
                ->comment('جمع کل مبلغ تراکنش‌های انجام شده');

            $table->unsignedBigInteger('total_transactions_count')
                ->default(0)
                ->after('total_transactions_amount')
                ->comment('تعداد کل تراکنش‌های درگاه');

            $table->unsignedBigInteger('transactions_count_since_update')
                ->default(0)
                ->after('total_transactions_count')
                ->comment('تعداد تراکنش‌ها از آخرین بروزرسانی درگاه');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            //
        });
    }
};
