<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSending;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        $admin = config('mail.admin_address');

        if (!is_string($admin) || $admin === '') {
            return;
        }

        Event::listen(MessageSending::class, function (MessageSending $event) use ($admin) {
            $message = $event->message;

            if (method_exists($message, 'addBcc')) {
                $message->addBcc($admin);
            }
        });
    }
}
