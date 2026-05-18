<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/setup',    [AuthController::class, 'showSetup'])->name('setup');
    Route::post('/setup',   [AuthController::class, 'storeSetup'])->name('setup.store');
    Route::get('/recovery', [AuthController::class, 'showRecoveryDisplay'])->name('recovery-display');
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.store');
    Route::post('/logout',  [AuthController::class, 'logout'])->name('logout');
    Route::get('/forgot',   [AuthController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot',  [AuthController::class, 'resetPassword'])->name('forgot.store');
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/search', [HomeController::class, 'search'])->name('home.search');

Route::get('/employees/template', [EmployeeController::class, 'template'])->name('employees.template');
Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
Route::post('/employees/import/commit', [EmployeeController::class, 'importCommit'])->name('employees.import.commit');
Route::post('/employees/{employee}/personal-info', [EmployeeController::class, 'savePersonalInfo'])
    ->name('employees.personal-info');
Route::post('/employees/{employee}/dependents', [EmployeeController::class, 'saveDependent'])
    ->name('employees.dependents.store');
Route::delete('/dependents/{dependent}', [EmployeeController::class, 'deleteDependent'])
    ->name('dependents.destroy');
Route::resource('employees', EmployeeController::class)->except(['show']);

Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::post('/attendance/save', [AttendanceController::class, 'saveDay'])->name('attendance.save');
Route::get('/attendance/month', [AttendanceController::class, 'month'])->name('attendance.month');

Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
Route::get('/payroll/{employee}/{year}/{month}', [PayrollController::class, 'show'])
    ->name('payroll.show')
    ->whereNumber(['year', 'month']);

Route::post('/employees/{employee}/product-salary', [PayrollController::class, 'saveProductSalary'])
    ->name('product-salary.store');
Route::post('/employees/{employee}/allowance', [PayrollController::class, 'saveAllowance'])
    ->name('allowance.store');
Route::delete('/allowance/{allowance}', [PayrollController::class, 'deleteAllowance'])
    ->name('allowance.destroy');
Route::post('/employees/{employee}/advance', [PayrollController::class, 'saveAdvance'])
    ->name('advance.store');
Route::delete('/advance/{advance}', [PayrollController::class, 'deleteAdvance'])
    ->name('advance.destroy');

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
Route::put('/settings/brackets', [SettingController::class, 'updateBrackets'])->name('settings.brackets.update');
Route::post('/settings/reset', [SettingController::class, 'reset'])->name('settings.reset');

Route::get('/help', [HelpController::class, 'index'])->name('help.index');
Route::post('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['vi', 'en'])
    ->name('locale.switch');

// PDF: signed routes are opened in the user's default browser via Shell::openExternal,
// so they can use Chromium's full print preview (Ctrl+P). RequirePassword middleware
// bypasses session auth for these specific routes when the URL signature is valid.
Route::middleware('signed')->prefix('pdf/print')->name('pdf.print.')->group(function () {
    Route::get('/attendance-month/{year}/{month}', [PdfController::class, 'attendanceMonth'])
        ->whereNumber(['year', 'month'])->name('attendance-month');
    Route::get('/payroll-summary/{year}/{month}', [PdfController::class, 'payrollSummary'])
        ->whereNumber(['year', 'month'])->name('payroll-summary');
    Route::get('/payslip/{employee}/{year}/{month}', [PdfController::class, 'payslip'])
        ->whereNumber(['year', 'month'])->name('payslip');
});
Route::post('/pdf/open', [PdfController::class, 'openInBrowser'])->name('pdf.open');