<?php

namespace App\Listeners\Professional;


use App\Events\Professional\ServiceRequestStatusChanged as Event;
use App\Jobs\Invoicing\ProcessOrder;
use App\Notifications\Directory\ServiceRequestAccepted;
use App\Notifications\Directory\ServiceRequestRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ServiceRequestStatusChanged implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle(Event $event)
    {
        if ($event->request->is_pending) {
            # it's still in the pending state
            throw new \UnexpectedValueException('The service requests is still in the pending state.');
        }
        $requestingCompany = $event->request->company;
        # get the specific requesting company
        $user = $requestingCompany->users()->firstOrFail();
        # get the first user in the requesting company
        if ($event->request->is_accepted) {
            $service = $event->request->service;
            # get the related service
            $serviceCompany = $service->user->company;
            # get the owning requesting company
            $customer = $serviceCompany->customers()->firstOrNew(['email' => $user->email]);
            # create the model, if required
            if (empty($customer->uuid)) {
                $customer->firstname = $user->firstname;
                $customer->lastname = $user->lastname;
                $customer->phone = $user->phone;
                $customer->saveOrFail();
                # save the new customer
            }
            if ($service->is_free) {
                $description = 'FREE Service!';
            } else {
                $description = 'PAID Service @ '.$service->cost_currency . number_format($service->cost_amount, 2) .
                    ($service->cost_frequency !== 'standard' ? ' per '.$service->cost_frequency : '');
            }
            $orderData = [
                'title' => 'Service Request for '.$service->title,
                'description' => $event->request->message ?: 'Requested service.',
                'currency' => $service->cost_currency,
                'amount' => $service->is_free ? 0 : $service->cost_amount,
                'reminder_on' => 0,
                'due_at' => null,
                'product_name' => $service->title,
                'product_description' => $description,
                'quantity' => 1,
                'unit_price' => $service->cost_amount
            ];
            $order = $serviceCompany->orders()->create($orderData);
            # create the model
            $order->customers()->sync([$customer->id]);
            # synchronise the customers as well
            dispatch(new ProcessOrder($order));
            # dispatch the order processing job
            $user->notify(new ServiceRequestAccepted($event->request));
        } else {
            $user->notify(new ServiceRequestRejected($event->request));
        }
        return;
    }
}