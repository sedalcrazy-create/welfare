<?php

namespace App\Providers;

use App\Events\PersonnelApproved;
use App\Events\PersonnelRejected;
use App\Listeners\SendBaleApprovalNotification;
use App\Listeners\SendBaleRejectionNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PersonnelApproved::class => [
            SendBaleApprovalNotification::class,
        ],
        PersonnelRejected::class => [
            SendBaleRejectionNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
