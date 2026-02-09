<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use App\Models\Center;
use App\Models\Unit;
use App\Models\Period;
use App\Models\Lottery;
use App\Models\Reservation;
use App\Models\Personnel;
use App\Models\Province;
use App\Policies\CenterPolicy;
use App\Policies\UnitPolicy;
use App\Policies\PeriodPolicy;
use App\Policies\LotteryPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\PersonnelPolicy;
use App\Policies\ProvincePolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register Policies
        Gate::policy(Center::class, CenterPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Period::class, PeriodPolicy::class);
        Gate::policy(Lottery::class, LotteryPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(Personnel::class, PersonnelPolicy::class);
        Gate::policy(Province::class, ProvincePolicy::class);
    }
}
