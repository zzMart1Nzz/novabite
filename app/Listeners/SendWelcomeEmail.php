<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
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
    public function handle(Registered $event): void
    {
        try {
            $homeUrl = rtrim((string) config('app.url'), '/').route('home', absolute: false);

            Mail::to($event->user)->send(new WelcomeEmail($event->user, $homeUrl));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
