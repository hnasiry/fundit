<?php

declare(strict_types=1);

use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
Route::get('/reports/top-users', [ReportController::class, 'topUsers']);
