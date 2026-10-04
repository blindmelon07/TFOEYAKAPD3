<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ChapterStatController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubDocumentController;
use App\Http\Controllers\Admin\ClubLetterheadController;
use App\Http\Controllers\Admin\ClubReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DuesPaymentController;
use App\Http\Controllers\Admin\DuesRateController;
use App\Http\Controllers\Admin\FormsController;
use App\Http\Controllers\Admin\FundAllocationController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipStepController;
use App\Http\Controllers\Admin\MissionController;
use App\Http\Controllers\Admin\PillarController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('admin/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('admin/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('admin/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('admin/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('admin/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('account', [AccountController::class, 'update'])->name('account.update');

    Route::get('forms', FormsController::class)->name('forms');
    Route::get('reports', ReportsController::class)->name('reports');

    Route::resource('clubs', ClubController::class)->except('show');
    Route::scopeBindings()->group(function () {
        Route::get('clubs/{club}/dues-rates', [DuesRateController::class, 'index'])->name('clubs.dues-rates.index');
        Route::post('clubs/{club}/dues-rates', [DuesRateController::class, 'store'])->name('clubs.dues-rates.store');
        Route::delete('clubs/{club}/dues-rates/{duesRate}', [DuesRateController::class, 'destroy'])->name('clubs.dues-rates.destroy');

        Route::resource('clubs.documents', ClubDocumentController::class)->except('show');
        Route::post('clubs/{club}/documents/{document}/duplicate', [ClubDocumentController::class, 'duplicate'])->name('clubs.documents.duplicate');
        Route::get('clubs/{club}/documents/{document}/download', [ClubDocumentController::class, 'download'])->name('clubs.documents.download');

        Route::get('clubs/{club}/reports', [ClubReportController::class, 'index'])->name('clubs.reports.index');
        Route::get('clubs/{club}/reports/{report}', [ClubReportController::class, 'show'])->name('clubs.reports.show');
        Route::get('clubs/{club}/reports/{report}/print', [ClubReportController::class, 'print'])->name('clubs.reports.print');
        Route::get('clubs/{club}/reports/{report}/word', [ClubReportController::class, 'word'])->name('clubs.reports.word');
        Route::get('clubs/{club}/reports/{report}/csv', [ClubReportController::class, 'csv'])->name('clubs.reports.csv');

        Route::get('clubs/{club}/letterhead', [ClubLetterheadController::class, 'show'])->name('clubs.letterhead.show');
        Route::post('clubs/{club}/letterhead', [ClubLetterheadController::class, 'store'])->name('clubs.letterhead.store');
        Route::delete('clubs/{club}/letterhead', [ClubLetterheadController::class, 'destroy'])->name('clubs.letterhead.destroy');
    });
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/dues', [DuesPaymentController::class, 'store'])->name('members.dues.store');
    Route::delete('dues/{duesPayment}', [DuesPaymentController::class, 'destroy'])->name('dues.destroy');

    Route::middleware('can:manage-site')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

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
});
