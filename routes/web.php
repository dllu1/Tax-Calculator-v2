<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/search', [HomeController::class, 'search'])->name('home.search');

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