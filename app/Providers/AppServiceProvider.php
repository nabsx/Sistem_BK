<?php

namespace App\Providers;

use App\Models\StudentViolation;
use App\Observers\StudentViolationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        StudentViolation::observe(StudentViolationObserver::class);
    }
}