<?php

namespace App\Listeners;

use App\Events\LoginEvent;
use App\Notifications\LoginNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoginListener
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
    public function handle(LoginEvent $event): void
    {
        $event->user->notify(new LoginNotification());
    }
}
