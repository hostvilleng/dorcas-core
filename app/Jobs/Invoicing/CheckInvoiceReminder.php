<?php

namespace App\Jobs\Invoicing;


use App\Jobs\Job;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

class CheckInvoiceReminder extends Job
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $builder = Order::whereNotNull('due_at')
                            ->where('reminder_on', 1)
                            ->where('is_quote', 0)
                            ->where('due_at', '>', Carbon::now()->format('Y-m-d'));
        # create the builder instance
        $now = Carbon::now();
        # get the current date
        foreach ($builder->cursor() as $order) {
            # loop through the orders for processing
            $diffDays = (int) $order->due_at->diff($now)->days;
            # get the difference in days
            $diffFromCreationDate = $now->diff($order->created_at);
            # get the difference from the order creation date
            if ($diffDays > 4 && $diffFromCreationDate->days % 4 !== 0) {
                # so long as we have more than 4 days to the due date, we send reminders every 4 days
                continue;
            }
            Queue::push(new SendReminder($order));
            # add the job
        }
        return;
    }
}
