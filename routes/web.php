<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ChapterStatController;
use App\Http\Controllers\Admin\FundAllocationController;
use App\Http\Controllers\Admin\MembershipStepController;
use App\Http\Controllers\Admin\MissionController;
use App\Http\Controllers\Admin\PillarController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('admin/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/settings')->name('dashboard');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('account', [AccountController::class, 'update'])->name('account.update');

    Route::resource('stats', ChapterStatController::class)
        ->except('show')
        ->parameters(['stats' => 'chapterStat']);
    Route::resource('pillars', PillarController::class)->except('show');
    Route::resource('missions', MissionController::class)->except('show');
    Route::resource('fund-allocations', FundAllocationController::class)
        ->except('show')
        ->parameters(['fund-allocations' => 'fundAllocation']);
    Route::resource('membership-steps', MembershipStepController::class)
        ->except('show')
        ->parameters(['membership-steps' => 'membershipStep']);
});
