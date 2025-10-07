<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\User\OrderController;

Route::get('orders/invoice/{token}', [OrderController::class, 'showInvoice'])->name('order.invoice');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin|api).*$');


/*
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});*/
