<?php

namespace App\Listeners\Admin;

use App\Models\Admin;
use Illuminate\Auth\Events\Registered;
use App\Notifications\Admin\PlayerWithNoAccountNotification;

class PlayerWithNoAccountListner
{
    private $admin;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event->user->profileable_type == 'player' && !$event->user->profileable->membership) {
            $admins = $this->admin->select('email')->get();
            \Notification::send($admins, new PlayerWithNoAccountNotification($event->user));
        }
    }
}
