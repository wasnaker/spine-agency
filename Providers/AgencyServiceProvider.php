<?php

declare(strict_types=1);

namespace Modules\Agency\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Agency\Listeners\LogAgencyActivity;

class AgencyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Event::listen(\Spine\Events\EntityCreated::class, LogAgencyActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogAgencyActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogAgencyActivity::class . '@deleted');
    }
}
