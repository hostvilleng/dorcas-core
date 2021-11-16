<?php

namespace App\Console;

use App\Jobs\Approval\Approvals;
use App\Jobs\Approval\ApprovalsJob;
use App\Jobs\Billing\AutoBilling;
use App\Jobs\Billing\AutoBillingReminder;
use App\Jobs\Finance\TranstrakMoveEntries;
use App\Jobs\Finance\TaxRun;
use App\Jobs\Invoicing\CheckInvoiceReminder;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Setup\ImportCountries::class,
        \App\Console\Commands\Setup\ImportStates::class,
        \App\Console\Commands\Setup\RolesAndPermissions::class,
        //\App\Console\Commands\Reports\PlatformStats::class,
        //\App\Console\Commands\Requests\leaveAction::class
        \App\Console\Commands\DorcasSetup::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


        # schedule to check for invoice reminders

        /*$schedule->call(function () {
            dispatch(new TranstrakMoveEntries());
        })->everyTenMinutes();*/
        # move Transtrak entries between databases

        // $schedule->call(function (){
        //     echo 'hello World';
        // })->everyMinute();

        // $schedule->call(function (){
        //   dispatch(new TaxRun());
        // })->daily();
        // $schedule->call(function (){
        //    dispatch(new ApprovalsJob());
        // })->everyMinute();

        // $schedule->command('dorcas:report-platform-stats')
        //             ->dailyAt('07:00')
        //             ->sendOutputTo(storage_path('logs/platform-report-output.log'));
        // $schedule->call(function () {
        //     dispatch(new AutoBillingReminder());
        // })->dailyAt('07:00');
        // # send auto-billing reminders
        // $schedule->call(function () {
        //     dispatch(new AutoBilling());
        // })->dailyAt('07:00');
        // # auto charge customers at this time
    }

}
