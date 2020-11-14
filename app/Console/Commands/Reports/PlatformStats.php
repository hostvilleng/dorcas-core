<?php

namespace App\Console\Commands\Reports;

use App\Mail\Reports\DailyReportEmail;
use App\Models\Domain;
use App\Models\DomainIssuance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PlatformStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:report-platform-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a report about various statistics and sends via email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * Total users
     * New users from yesterday
     * Total users activated
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting report daily stats...');
        $yesterdayRange = [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()];
        $data = [];
     # the data container
        $this->line('Preparing to read user stats...');
        $userBuilder = new User();
        # the user builder
        $data['total users'] = (clone $userBuilder)->count();
        $data['total verified users'] = (clone $userBuilder)->where('is_verified', 1)->count();
        $data['new users'] = (clone $userBuilder)->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        $data['new users (verified)'] = (clone $userBuilder)->where('is_verified', 1)
                                                            ->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        $data['new professionals'] = (clone $userBuilder)->where('is_professional', 1)
                                                        ->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        $data['new vendors'] = (clone $userBuilder)->where('is_vendor', 1)
                                                    ->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        $this->line('Preparing to check domain purchase stats...');
        $domainBuilder = new Domain();
        $data['total domains'] = (clone $domainBuilder)->count();
        $data['new domains'] = (clone $domainBuilder)->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        # builder for the activations
        $this->line('Preparing to check sub-domain issuance stats...');
        $subDomainBuilder = new DomainIssuance();
        $data['total sub-domains'] = (clone $subDomainBuilder)->count();
        $data['new sub-domains'] = (clone $subDomainBuilder)->whereBetween('created_at', [$yesterdayRange[0]->format('Y-m-d H:i:s'), $yesterdayRange[1]->format('Y-m-d H:i:s')])->count();
        $headers = collect(array_keys($data))->map(function ($e) { return title_case($e); })->all();
        $this->table($headers, [$data]);
        $this->line('Emailing report to bosses...');
        Mail::to(config('dorcas-api.report_recipients.to'))
                ->cc(config('dorcas-api.report_recipients.cc'))
                ->send(new DailyReportEmail($data));
        return;
    }
}