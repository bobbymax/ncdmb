<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ============================================
// PROCESSCARD AUTOMATION - SCHEDULED TASKS
// ============================================

// Daily Reconciliation (runs every day at 11:00 PM)
Schedule::command('accounting:reconcile daily')
    ->dailyAt('23:00')
    ->description('Daily fund reconciliation based on ProcessCard rules');

// Weekly Reconciliation (runs every Monday at 11:00 PM)
Schedule::command('accounting:reconcile weekly')
    ->weeklyOn(1, '23:00')
    ->description('Weekly fund reconciliation based on ProcessCard rules');

// Monthly Reconciliation (runs on 1st of each month at 11:00 PM)
Schedule::command('accounting:reconcile monthly')
    ->monthlyOn(1, '23:00')
    ->description('Monthly fund reconciliation based on ProcessCard rules');

// Quarterly Reconciliation
Schedule::command('accounting:reconcile quarterly')
    ->quarterly()
    ->at('23:00')
    ->description('Quarterly fund reconciliation based on ProcessCard rules');

// Batch Processing (runs every day at 11:30 PM)
Schedule::command('accounting:process-batch')
    ->dailyAt('23:30')
    ->description('Process batch priority ProcessCards');

// Period Closing (runs on 5th of each month at midnight)
Schedule::command('accounting:close-period')
    ->monthlyOn(5, '00:00')
    ->description('Auto-close previous month accounting period');
