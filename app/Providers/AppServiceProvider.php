<?php

namespace App\Providers;

use App\Models\StudentViolation;
use App\Observers\StudentViolationObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user) {
            return $user->hasRole(['super admin', 'super_admin']) ? true : null;
        });

        StudentViolation::observe(StudentViolationObserver::class);
    }
}
