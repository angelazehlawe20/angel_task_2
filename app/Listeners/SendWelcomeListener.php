<?php

namespace App\Listeners;

use App\Events\RegisterEvent;
use App\Notifications\RegisterNotification;
use App\Notifications\verificationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RegisterEvent $event): void
    {
        $event->user->notify(new RegisterNotification());
    }

}
